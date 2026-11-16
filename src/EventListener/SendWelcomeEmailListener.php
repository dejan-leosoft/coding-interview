<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Event\PlayerCreated;
use App\Mailer\WelcomeEmailMailer;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: PlayerCreated::class)]
final class SendWelcomeEmailListener
{
    public function __construct(
        private readonly WelcomeEmailMailer $mailer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(PlayerCreated $event): void
    {
        try {
            $this->mailer->send($event->email, $event->username);
        } catch (\Throwable $exception) {
            $this->logger->error('Welcome email delivery failed.', [
                'playerId' => $event->playerId,
                'exception' => $exception,
            ]);
        }
    }
}
