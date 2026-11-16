<?php

declare(strict_types=1);

namespace App\Domain\Payment;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/** Every implementation is tagged so PaymentFactory can look it up by class. */
#[AutoconfigureTag(Payment::TAG)]
interface Payment
{
    public const TAG = 'app.payment_provider';

    /**
     * Initiate a deposit through this provider and return its reference and status.
     * Validate the amount in integer cents and the currency before creating it.
     * Providers that do not support deposits throw BadMethodCallException.
     *
     * @return array<string, mixed>
     */
    public function create(int $amountCents, string $currency): array;

    /**
     * Initiate a withdrawal through this provider and return its reference and status.
     * Validate the amount in integer cents and the currency before creating it.
     * Providers that do not support withdrawals throw BadMethodCallException.
     *
     * @return array<string, mixed>
     */
    public function withdraw(int $amountCents, string $currency): array;

    /**
     * Verify that a callback came from the provider using its raw body and headers.
     * Return normally on success; throw when verification fails.
     * Verification must not change payment state.
     *
     * @param array<string, list<string>> $headers
     */
    public function signature(string $payload, array $headers): void;

    /**
     * Process a provider notification and return the payment reference and status.
     * Call signature() before parsing the body or changing payment state.
     * Repeated callbacks must not apply the same payment twice, and conflicting
     * final statuses must be rejected.
     *
     * @param array<string, list<string>> $headers
     * @return array<string, mixed>
     */
    public function callback(string $payload, array $headers): array;
}
