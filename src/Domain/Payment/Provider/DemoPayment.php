<?php

declare(strict_types=1);

namespace App\Domain\Payment\Provider;

use App\Domain\Payment\Payment;

/** Placeholder provider; the implemented Codonix provider is described in docs/03-payment-provider.md. */
final class DemoPayment implements Payment
{
    public function create(int $amountCents, string $currency): array
    {
        // TODO: Create a payment through the provider and return its reference/status.
        throw new \BadMethodCallException('Payment creation is not implemented.');
    }

    public function withdraw(int $amountCents, string $currency): array
    {
        throw new \BadMethodCallException('Payment withdrawals are not implemented.');
    }

    public function signature(string $payload, array $headers): void
    {
        throw new \BadMethodCallException('Payment signature verification is not implemented.');
    }

    public function callback(string $payload, array $headers): array
    {
        // TODO: Verify the signature using the original payload and headers,
        // then update the payment status, handling repeated callbacks safely.
        throw new \BadMethodCallException('Payment callbacks are not implemented.');
    }
}
