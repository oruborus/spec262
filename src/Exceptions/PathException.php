<?php

declare(strict_types=1);

namespace Oru\Spec262\Exceptions;

use RuntimeException;

final class PathException extends RuntimeException
{
    public static function noCanonicalizedAbsolutePathName(string $pathname): static
    {
        return new static("Could not convert the provided path `{$pathname}` to the canonicalized absolute pathname");
    }
}
