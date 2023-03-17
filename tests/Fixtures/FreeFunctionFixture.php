<?php

declare(strict_types=1);

namespace Tests\Fixtures;

/**
 * @see https://tc39.es/ecma262/#sec-tozeropaddeddecimalstring
 */
function toZeroPaddedDecimalString(int $n, int $minLength): string
{
    // 1. Let S be the String representation of n, formatted as a decimal number.
    $s = (string) $n;

    // 2. Return ! StringPad(S, 𝔽(minLength), "0", start).
    return stringPad($s, $minLength, '0', START);
}
