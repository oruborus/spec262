<?php

declare(strict_types=1);

namespace Oru\Spec262\Visitors;

use Jfcherng\Diff\DiffHelper;
use Oru\Spec262\Formatters\FormatterFactory;
use Oru\Spec262\Specifications\SpecificationFactory;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Symfony\Component\Console\Style\StyleInterface;

use function is_null;
use function preg_match_all;

use const PREG_SET_ORDER;

final class MethodVisitor extends NodeVisitorAbstract
{
    private NodeTraverser $traverser;

    private StatementListVisitor $visitor;

    public function __construct(
        private StyleInterface $io,
        private string $file
    ) {
        $this->traverser = new NodeTraverser;
        $this->visitor = new StatementListVisitor();
        $this->traverser->addVisitor($this->visitor);
    }

    public function leaveNode(Node $node): void
    {
        if (!$node instanceof ClassMethod) {
            return;
        }

        foreach ($this->allUrlAndEsidPairs($node) as ['url' => $url, 'esid' => $esid]) {
            if ($esid === '') {
                continue;
            }

            $formatter     = FormatterFactory::make($url);
            $specification = SpecificationFactory::make($url);

            foreach ($specification->getAlgForEsid($esid) as $alg) {
                if ($result = DiffHelper::calculate($this->allComments($node), $formatter->format($alg->textContent), 'unified', ['context' => 0])) {
                    $this->io->section($this->file);
                    $this->io->text($result);
                }
            }
        }
    }

    /**
     * @return string[]
     */
    private function allComments(ClassMethod $method): array
    {
        $stmts = $method->getStmts();
        if (is_null($stmts)) {
            return [];
        }

        $this->traverser->traverse($stmts);
        $comments = $this->visitor->comments();
        $this->visitor->reset();

        return $comments;
    }

    /**
     * @return array{url:string, esid:string}[]
     */
    private function allUrlAndEsidPairs(ClassMethod $method): array
    {
        if (is_null($doc = $method->getDocComment())) {
            return [];
        }

        $re = '/^(?:\s*\/\*\*|\s*\*) @see (?<url>\S*)#(?<esid>\S*)\s*(?:\*\/)?$/m';
        if (false === preg_match_all($re, (string) $doc, $matches, PREG_SET_ORDER, 0)) {
            return [];
        }

        /**
         * @var array{url:string, esid:string}[]
         */
        return $matches;
    }
}
