<?php

declare(strict_types=1);

namespace App\Domain\Payment;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

/**
 * Returns a provider enabled for an operation in Config/provider.yaml.
 */
final class PaymentFactory
{
    /**
     * @param array<string, array<string, class-string<Payment>>> $providers Operation => provider name => provider class
     * @param ContainerInterface $payments All Payment implementations, keyed by class
     */
    public function __construct(
        #[Autowire(param: 'payment.providers')]
        private readonly array $providers,
        #[AutowireLocator(Payment::TAG)]
        private readonly ContainerInterface $payments,
    ) {
    }

    /**
     * @throws \InvalidArgumentException When the operation or provider is not listed in Config/provider.yaml.
     * @throws \LogicException When the listed class is not a registered Payment implementation.
     */
    public function create(string $provider, string $operation = 'deposit'): Payment
    {
        $providers = $this->providers[$operation]
            ?? throw new \InvalidArgumentException('Unsupported payment operation.');
        $class = $providers[$provider]
            ?? throw new \InvalidArgumentException(sprintf('Unsupported payment provider for %s.', $operation));

        if (!$this->payments->has($class)) {
            throw new \LogicException(sprintf(
                'Payment provider "%s" is mapped to "%s", which is not a registered %s implementation.',
                $provider,
                $class,
                Payment::class,
            ));
        }

        return $this->payments->get($class);
    }

    /**
     * @return list<string> Provider names enabled for the given operation
     */
    public function names(string $operation = 'deposit'): array
    {
        $providers = $this->providers[$operation]
            ?? throw new \InvalidArgumentException('Unsupported payment operation.');

        return array_keys($providers);
    }
}
