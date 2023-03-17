<?php

declare(strict_types=1);

namespace Oru\Spec262\Contracts;

use DOMNode;

interface Specification
{
    /**
     * @return DOMNode[]
     */
    public function getAlgForEsid(string $esid): array;
}
