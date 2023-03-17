<?php

declare(strict_types=1);

namespace Oru\Spec262\Specifications;

use Oru\Spec262\Contracts\Specification;
use RuntimeException;

final class SpecificationFactory
{
    public static function make(string $specification): Specification
    {
        return match ($specification) {
            '',
            'https://tc39.es/ecma262/' => new CurrentSpecification(),
            default => throw new RuntimeException("No Specification for `{$specification}` found")
        };
    }
}
