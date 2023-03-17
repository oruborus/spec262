<?php

declare(strict_types=1);

namespace Oru\Spec262\Visitors;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

use function ltrim;
use function str_starts_with;
use function substr;

final class StatementListVisitor extends NodeVisitorAbstract
{
    /**
     * @param string[] $comments
     */
    public function __construct(
        private array $comments = []
    ) {
    }

    public function leaveNode(Node $node): void
    {
        if (!$node instanceof Node\Stmt) {
            return;
        }

        foreach ($node->getComments() as $comment) {
            $commentText = ltrim($comment->getText());

            if (!str_starts_with($commentText, '//')) {
                continue;
            }

            $this->comments[] = ltrim(substr($commentText, 2));
        }

        return;
    }

    /**
     * @return string[]
     */
    public function comments(): array
    {
        return $this->comments;
    }

    public function reset(): void
    {
        $this->comments = [];
    }
}
