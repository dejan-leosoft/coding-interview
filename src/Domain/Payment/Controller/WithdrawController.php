<?php

declare(strict_types=1);

namespace App\Domain\Payment\Controller;

use App\Domain\Payment\PaymentFactory;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class WithdrawController extends AbstractController
{
    public function __construct(
        private readonly PaymentFactory $paymentFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    /** Example body: {"amountCents": 1500, "currency": "EUR"}. */
    #[Route('/api/payments/{provider}/withdraw', name: 'payment_withdraw', methods: ['POST'])]
    public function withdraw(string $provider, Request $request): JsonResponse
    {
        try {
            $payload = $request->toArray();
        } catch (JsonException) {
            return $this->json(['error' => 'The request must contain valid JSON.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $amountCents = $payload['amountCents'] ?? null;
        $currency = $payload['currency'] ?? null;

        if (!is_int($amountCents) || $amountCents <= 0) {
            return $this->json(['error' => 'Amount must be a positive integer in cents.'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!is_string($currency) || preg_match('/^[A-Z]{3}$/D', $currency) !== 1) {
            return $this->json(['error' => 'Currency must be a three-letter uppercase code.'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $payment = $this->paymentFactory->create($provider, 'withdraw');
            $result = $payment->withdraw($amountCents, $currency);
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['error' => $exception->getMessage()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\BadMethodCallException $exception) {
            return $this->json(['error' => $exception->getMessage()], JsonResponse::HTTP_NOT_IMPLEMENTED);
        } catch (\Throwable $exception) {
            $this->logger->error('Payment withdrawal failed.', ['exception' => $exception]);

            return $this->json(['error' => 'Unable to process withdrawal.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json($result, JsonResponse::HTTP_CREATED);
    }
}
