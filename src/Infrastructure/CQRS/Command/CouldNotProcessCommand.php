<?php

declare(strict_types=1);

namespace App\Infrastructure\CQRS\Command;

final class CouldNotProcessCommand extends \RuntimeException
{
    public static function withReason(string $reason): self
    {
        return new self($reason);
    }
}
