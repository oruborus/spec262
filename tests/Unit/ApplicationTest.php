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

    #[Test]
    public function displaysPathName(): void
    {
        $this->tester->execute(['path' => 'tests/Fixtures/FreeFunctionFixture.php']);

        $this->tester->assertCommandIsSuccessful();
        $this->assertStringContainsString(
            'tests' . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'FreeFunctionFixture.php',
            $this->tester->getDisplay()
        );
    }

    #[Test]
    public function showsErrorMessageWhenPathCanNotBeAbsolutized(): void
    {
        $this->tester->execute(['path' => '%%DOES NOT CONVERT%%']);

        $this->assertStringContainsString(
            PathException::noCanonicalizedAbsolutePathName('%%DOES NOT CONVERT%%')->getMessage(),
            $this->tester->getDisplay()
        );
    }

    #[Test]
    public function canHandleSymlink(): void
    {
        $this->tester->execute(['path' => 'tests/Fixtures/SymlinkClassFixture']);

        $this->tester->assertCommandIsSuccessful();
    }
}
