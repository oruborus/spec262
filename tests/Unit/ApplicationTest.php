<?php

declare(strict_types=1);

namespace Tests\Unit;

use Oru\Spec262\Application;
use Oru\Spec262\Exceptions\PathException;
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
        $app = new Application();
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

    #[Test]
    public function displaysPathName(): void
    {
        $path = 'tests' . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'FreeFunctionFixture.php';
        $this->execute(['path' => $path]);

        $this->assertCommandIsSuccessful();
        $this->assertOutputContains($path);
    }

    #[Test]
    public function showsErrorMessageWhenPathCanNotBeAbsolutized(): void
    {
        $path = '%%DOES NOT CONVERT%%';
        $this->execute(['path' => $path]);

        $this->assertOutputContains(PathException::noCanonicalizedAbsolutePathName($path)->getMessage());
    }

    #[Test]
    public function canHandleSymlink(): void
    {
        $path = 'tests' . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'SymlinkClassFixture';
        $this->execute(['path' => $path]);

        $this->assertCommandIsSuccessful();
    }
}
