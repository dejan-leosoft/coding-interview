<?php

declare(strict_types=1);

namespace App\Domain\Payment\Exception;

/** Expected callback rejection; HTTP translation belongs to the controller. */
final class CallbackException extends \RuntimeException
{
    public const SIGNATURE = 'signature';
    public const JSON = 'json';
    public const NOT_FOUND = 'not_found';
    public const CONFLICT = 'conflict';

    public function __construct(public readonly string $reason, string $message)
    {
        parent::__construct($message);
    }
}
