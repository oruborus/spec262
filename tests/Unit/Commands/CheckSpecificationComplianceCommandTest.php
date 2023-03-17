<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use Oru\Spec262\Commands\CheckSpecificationComplianceCommand;
use Oru\Spec262\Exceptions\PathException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

use const DIRECTORY_SEPARATOR;

#[CoversClass(CheckSpecificationComplianceCommand::class)]
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
    public function showsErrorMessageWhenPathDoesNotExist(): void
    {
        $this->expectException(PathException::class);

        $this->tester->execute(['path' => 'tests/Fixtures/non-existing']);
    }
}
