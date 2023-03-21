<?php

declare(strict_types=1);

namespace Oru\Spec262\Exceptions;

use RuntimeException;

final class PathException extends RuntimeException
{
    public static function noCanonicalizedAbsolutePathName(string $pathname): never
    {
        throw new static("Could not convert the provided path `{$pathname}` to the canonicalized absolute pathname");
    }

    public static function couldNotResolveLinkPath(string $pathname): never
    {
        throw new static("Could not resolve the provided link path `{$pathname}`");
    }
}
