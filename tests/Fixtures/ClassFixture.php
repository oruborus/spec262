<?php

declare(strict_types=1);

namespace Tests\Fixtures;

final class StringPrototype
{
    /**
     * @see https://tc39.es/ecma262/#sec-string.prototype.concat
     */
    function concat(mixed ...$args): string
    {
        // 1. Let O be ? RequireObjectCoercible(this value).
        $o = requireObjectCoercible($this);

        // 2. Let S be ? ToString(O).
        $s = toString($o);

        // 3. Let R be S.
        $r = $s;

        // 4. For each element next of args, do
        foreach ($args as $next) {
            // a. Let nextString be ? ToString(next).
            $nextSring = toString($next);

            // b. Set R to the string-concatenation of R and nextString.
            $r .= $nextSring;
        }

        // 5. Return R.
        return $r;
    }
}
