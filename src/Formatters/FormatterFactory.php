<?php

declare(strict_types=1);

namespace Oru\Spec262\Formatters;

use Oru\Spec262\Contracts\Formatter;
use RuntimeException;

final class FormatterFactory
{
    public static function make(string $specification): Formatter
    {
        return match ($specification) {
            '',
            'https://tc39.es/ecma262/' => new CurrentFormatter(),
            default => throw new RuntimeException("No formatter for `{$specification}` found")
        };
    }
}
