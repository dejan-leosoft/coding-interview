<?php

declare(strict_types=1);

namespace App\Domain\Payment\Controller;

use App\Domain\Payment\PaymentFactory;
use App\Domain\Payment\Provider\CodonixPayment;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class DepositController extends AbstractController
{
    public function __construct(
        private readonly PaymentFactory $paymentFactory,
        private readonly LoggerInterface $logger,
    )
    {
    }

    /** Example body: {"amountCents": 1500, "currency": "EUR"}. */
    #[Route('/api/payments/{provider}', name: 'payment_create', methods: ['POST'])]
    public function create(string $provider, Request $request): JsonResponse
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

        if ($provider === 'codonix') {
            if ($amountCents < CodonixPayment::MIN_AMOUNT || $amountCents > CodonixPayment::MAX_AMOUNT) {
                return $this->json(['error' => 'Amount must be between 50 and 1000000 cents.'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
            if (!in_array($currency, CodonixPayment::CURRENCIES, true)) {
                return $this->json(['error' => 'Currency must be EUR, USD or GBP.'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        try {
            $payment = $this->paymentFactory->create($provider, 'deposit');
            $result = $payment->create($amountCents, $currency);
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['error' => $exception->getMessage()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\BadMethodCallException $exception) {
            return $this->json(['error' => $exception->getMessage()], JsonResponse::HTTP_NOT_IMPLEMENTED);
        } catch (\Throwable $exception) {
            $this->logger->error('Payment request failed.', ['exception' => $exception]);

            return $this->json(['error' => 'Unable to process payment.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json($result, JsonResponse::HTTP_CREATED);
    }
}
