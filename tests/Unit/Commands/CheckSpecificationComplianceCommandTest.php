<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use Oru\Spec262\Commands\CheckSpecificationComplianceCommand;
use Oru\Spec262\Exceptions\PathException;
use Oru\Spec262\Formatters\CurrentFormatter;
use Oru\Spec262\Formatters\FormatterFactory;
use Oru\Spec262\Specifications\CurrentSpecification;
use Oru\Spec262\Specifications\SpecificationFactory;
use Oru\Spec262\Visitors\FunctionVisitor;
use Oru\Spec262\Visitors\MethodVisitor;
use Oru\Spec262\Visitors\StatementListVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;


use const DIRECTORY_SEPARATOR;

#[CoversClass(CheckSpecificationComplianceCommand::class)]
#[UsesClass(PathException::class)]
#[UsesClass(CurrentFormatter::class)]
#[UsesClass(FormatterFactory::class)]
#[UsesClass(CurrentSpecification::class)]
#[UsesClass(SpecificationFactory::class)]
#[UsesClass(FunctionVisitor::class)]
#[UsesClass(MethodVisitor::class)]
#[UsesClass(StatementListVisitor::class)]
final class CheckSpecificationComplianceCommandTest extends TestCase
{
    #[Test]
    public function displaysPathName(): void
    {
        $this->tester->execute(['path' => 'tests/Fixtures/FreeFunctionFixture.php']);

        $this->tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('tests' . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'FreeFunctionFixture.php', $this->tester->getDisplay());
    }

    #[Test]
    public function showsErrorMessageWhenPathCanNotBeAbsolutized(): void
    {
        $this->expectException(PathException::class);

        $this->tester->execute(['path' => '%%DOES NOT CONVERT%%']);
    }

    #[Test]
    public function showsErrorMessageWhenSymLinkCannotBeResolved(): void
    {
        $this->expectException(PathException::class);

        $this->tester->execute(['path' => 'tests/Fixtures/SYMLINK']);
    }
}
