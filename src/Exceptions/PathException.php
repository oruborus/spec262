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

    public static function noFileOrDirectory(string $pathname): never
    {
        throw new static("File or directory `{$pathname}` does not exist");
    }
}
