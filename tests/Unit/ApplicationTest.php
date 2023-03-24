<?php

declare(strict_types=1);

namespace Tests\Unit;

use Oru\Spec262\Application;
use Oru\Spec262\Exceptions\PathException;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

use const DIRECTORY_SEPARATOR;

#[CoversClass(Application::class)]
#[UsesClass(\Oru\Spec262\Exceptions\PathException::class)]
#[UsesClass(\Oru\Spec262\Formatters\CurrentFormatter::class)]
#[UsesClass(\Oru\Spec262\Formatters\FormatterFactory::class)]
#[UsesClass(\Oru\Spec262\PathResolver::class)]
#[UsesClass(\Oru\Spec262\Specifications\CurrentSpecification::class)]
#[UsesClass(\Oru\Spec262\Specifications\SpecificationFactory::class)]
#[UsesClass(\Oru\Spec262\Visitors\FunctionVisitor::class)]
#[UsesClass(\Oru\Spec262\Visitors\MethodVisitor::class)]
#[UsesClass(\Oru\Spec262\Visitors\StatementListVisitor::class)]
final class ApplicationTest extends TestCase
{
    /**
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private CommandTester $tester;

    #[Before]
    public function initializeTester(): void
    {
        $app = new Application(
            (new ParserFactory())->create(ParserFactory::ONLY_PHP7),
            new NodeTraverser()
        );
        $app->setAutoExit(false);
        $this->tester = new CommandTester($app);
    }

    private function assertOutputContains(string $needle, string $message = ''): void
    {
        $this->assertStringContainsString($needle, $this->tester->getDisplay(), $message);
    }

    /**
     * @psalm-suppress UnusedReturnValue
     */
    private function execute(array $input, array $options = []): int
    {
        return $this->tester->execute($input, $options);
    }

    private function assertCommandIsSuccessful(string $message = ''): void
    {
        $this->tester->assertCommandIsSuccessful($message);
    }
}
