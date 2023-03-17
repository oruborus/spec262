<?php

declare(strict_types=1);

namespace Oru\Spec262\Specifications;

use DOMDocument;
use DOMNode;
use Oru\Spec262\Contracts\Specification;

use function is_null;
use function iterator_to_array;
use function libxml_use_internal_errors;

final class CurrentSpecification implements Specification
{
    /**
     * @return DOMNode[]
     */
    public function getAlgForEsid(string $esid): array
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->strictErrorChecking = false;
        libxml_use_internal_errors(true);
        $dom->loadHTMLFile('./vendor/tc39/ecma262/spec.html', LIBXML_PARSEHUGE | LIBXML_NOWARNING);
        libxml_use_internal_errors(false);

        $clauses = $dom->getElementById($esid)?->getElementsByTagName('emu-alg');
        if (is_null($clauses)) {
            return [];
        }

        /**
         * @var DOMNode[]
         */
        return iterator_to_array($clauses);
    }
}
