<?php

declare(strict_types=1);

namespace Tests\Unit;

use Oru\Spec262\PathResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function realpath;

use const DIRECTORY_SEPARATOR;

#[CoversClass(PathResolver::class)]
#[UsesClass(\Oru\Spec262\Exceptions\PathException::class)]
final class PathResolverTest extends TestCase
{
    #[Test]
    public function resolvesASinglePathToAFile(): void
    {
        $path = 'tests' . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'FreeFunctionFixture.php';
        $expected = [realpath($path)];
        $pathResolver = new PathResolver();

        $this->assertEqualsCanonicalizing($expected, $pathResolver->resolvePaths([$path]));
    }

    #[Test]
    public function resolvesASinglePathToADirectory(): void
    {
        $path = 'tests' . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'DirectoryWithTwoFiles';
        $expected = [realpath($path . DIRECTORY_SEPARATOR . 'EmptyFile1.php'), realpath($path . DIRECTORY_SEPARATOR . 'EmptyFile2.php')];
        $pathResolver = new PathResolver();

        $this->assertEqualsCanonicalizing($expected, $pathResolver->resolvePaths([$path]));
    }

    #[Test]
    public function resolvesASinglePathToASymlinkToAFile(): void
    {
        $target = 'tests' . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'ClassFixture.php';
        $path = 'tests' . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'SymlinkClassFixture';

        $expected = [realPath($target)];
        $pathResolver = new PathResolver();

        $this->assertEqualsCanonicalizing($expected, $pathResolver->resolvePaths([$path]));
    }

    #[Test]
    public function resolvesASinglePathToASymlinkToASymlinkToAFile(): void
    {
        $target = 'tests' . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'ClassFixture.php';
        $path = 'tests' . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'SymlinkSymlinkClassFixture';

        $expected = [realPath($target)];
        $pathResolver = new PathResolver();

        $this->assertEqualsCanonicalizing($expected, $pathResolver->resolvePaths([$path]));
    }

    #[Test]
    public function resolvesASinglePathToASymlinkToADirectory(): void
    {
        $path = 'tests' . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'SymlinkDirectoryWithTwoFiles';
        $expected = [realpath($path . DIRECTORY_SEPARATOR . 'EmptyFile1.php'), realpath($path . DIRECTORY_SEPARATOR . 'EmptyFile2.php')];
        $pathResolver = new PathResolver();

        $this->assertEqualsCanonicalizing($expected, $pathResolver->resolvePaths([$path]));
    }

    #[Test]
    public function failsOnSymlinkLoops(): void
    {
        $path = 'tests' . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'SymlinkLoop1';
        $expected = [];
        $pathResolver = new PathResolver();

        $this->assertEqualsCanonicalizing($expected, $pathResolver->resolvePaths([$path]));
        $this->assertEqualsCanonicalizing([$path], $pathResolver->getFailedPaths());
    }

    #[Test]
    public function doesIgnoreAlreadyCheckedPaths(): void
    {
        $path = 'tests' . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'DirectoryWithTwoFiles';
        $pathResolver = new PathResolver();
        $expected = $pathResolver->resolvePaths([$path]);
        $pathResolver = new PathResolver();

        $this->assertEqualsCanonicalizing($expected, $pathResolver->resolvePaths([$path, $path]));
    }

    // TODO: Test failing open dir
}
