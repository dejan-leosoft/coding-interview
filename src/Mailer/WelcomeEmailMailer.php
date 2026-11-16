<?php

declare(strict_types=1);

namespace App\Mailer;

use Psr\Log\LoggerInterface;

/** Fake mailer for the coding exercise; replace with a delivery transport when needed. */
final class WelcomeEmailMailer
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function send(string $email, string $username): void
    {
        $this->logger->info('Welcome email simulated.', [
            'recipient' => $email,
            'subject' => 'Welcome!',
            'body' => sprintf('Welcome, %s! Your player account is ready.', $username),
        ]);
    }
}
