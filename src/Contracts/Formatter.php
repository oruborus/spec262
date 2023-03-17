<?php

declare(strict_types=1);

namespace Oru\Spec262\Contracts;

interface Formatter
{
    /**
     * @return string[]
     */
    public function format(string $content): array;
}
