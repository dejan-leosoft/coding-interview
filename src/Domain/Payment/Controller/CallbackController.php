<?php

declare(strict_types=1);

namespace App\Domain\Payment\Controller;

use App\Domain\Payment\PaymentFactory;
use App\Domain\Payment\Exception\CallbackException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CallbackController extends AbstractController
{
    public function __construct(
        private readonly PaymentFactory $paymentFactory,
        private readonly LoggerInterface $logger,
    )
    {
    }

    #[Route('/api/payments/{provider}/callback', name: 'payment_callback', methods: ['POST'])]
    public function callback(string $provider, Request $request): JsonResponse
    {
        try {
            $payment = $this->paymentFactory->create($provider, 'deposit');
            // Preserve the original body so providers can verify their signatures.
            $result = $payment->callback($request->getContent(), $request->headers->all());
        } catch (CallbackException $exception) {
            $status = match ($exception->reason) {
                CallbackException::SIGNATURE => JsonResponse::HTTP_FORBIDDEN,
                CallbackException::JSON => JsonResponse::HTTP_BAD_REQUEST,
                CallbackException::NOT_FOUND => JsonResponse::HTTP_NOT_FOUND,
                CallbackException::CONFLICT => JsonResponse::HTTP_CONFLICT,
            };

            return $this->json(['error' => $exception->getMessage()], $status);
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['error' => $exception->getMessage()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\BadMethodCallException $exception) {
            return $this->json(['error' => $exception->getMessage()], JsonResponse::HTTP_NOT_IMPLEMENTED);
        } catch (\Throwable $exception) {
            $this->logger->error('Payment request failed.', ['exception' => $exception]);

            return $this->json(['error' => 'Unable to process payment.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json($result);
    }
}
