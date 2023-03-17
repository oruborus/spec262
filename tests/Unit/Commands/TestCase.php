<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase as BaseTestCase;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

use function is_null;

abstract class TestCase extends BaseTestCase
{
    protected Application $application;

    protected Command $command;

    protected CommandTester $tester;

    /**
     * @psalm-param non-empty-string $name
     * 
     * @psalm-suppress InternalMethod
     * 
     * @internal
     */
    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->command     = $this->instantiateCommandFromCoveredClassAttribute();
        $this->tester      = new CommandTester($this->command);
        $this->application = new Application();
        $this->application->add($this->command);
    }

    /**
     * @throws RuntimeException When TestCase does not have a `CoveredClass` attributes that encapsulates a `Command`
     */
    private function instantiateCommandFromCoveredClassAttribute(): Command
    {
        $commandClassName = null;

        $testCaseReflection = new ReflectionClass(static::class);
        foreach ($testCaseReflection->getAttributes(CoversClass::class) as $reflectionAttribute) {
            $coveredClassReflection = new ReflectionClass($reflectionAttribute->newInstance()->className());
            if ($coveredClassReflection->isSubclassOf(Command::class)) {
                $commandClassName = $coveredClassReflection->getName();
                break;
            }
        }

        if (is_null($commandClassName)) {
            throw new RuntimeException('Could not find a covered `Command`. Did us use a `CoversClass` attribute?');
        }

        /**
         * @psalm-suppress UnsafeInstantiation
         */
        return new $commandClassName;
    }
}
