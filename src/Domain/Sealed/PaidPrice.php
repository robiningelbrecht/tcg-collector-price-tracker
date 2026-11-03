<?php

declare(strict_types=1);

namespace App\Domain\Sealed;

use App\Infrastructure\CQRS\Command\Deserialize\CouldNotDeserializeCommand;

final readonly class PaidPrice
{
    public static function fromPayload(mixed $value): ?float
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            return null;
        }

        $value = str_replace(',', '.', trim((string) $value));
        if ('' === $value) {
            return null;
        }

        $paidPrice = filter_var($value, FILTER_VALIDATE_FLOAT);
        if (false === $paidPrice || $paidPrice < 0) {
            throw CouldNotDeserializeCommand::invalidPayload('The paid price must be a positive amount.');
        }

        return $paidPrice;
    }
}
