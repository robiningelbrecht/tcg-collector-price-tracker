<?php

declare(strict_types=1);

namespace App\Domain\Collection;

final readonly class VariantLabel
{
    public static function from(?string $type, ?string $subtype, ?string $size, ?string $stamps): string
    {
        $label = match ($type) {
            'holo' => 'Holo',
            'reverse' => 'Reverse Holo',
            'normal' => 'Normal',
            null => 'Unknown variant',
            default => self::humanize($type),
        };

        $details = array_map(self::humanize(...), array_values(array_filter([
            $subtype,
            'standard' === $size ? null : $size,
            ...(null === $stamps ? [] : explode(',', $stamps)),
        ])));

        return [] === $details ? $label : sprintf('%s (%s)', $label, implode(', ', $details));
    }

    private static function humanize(string $value): string
    {
        return ucwords(str_replace('-', ' ', $value));
    }
}
