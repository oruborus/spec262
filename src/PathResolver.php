<?php

declare(strict_types=1);

namespace Oru\Spec262;

use function array_filter;
use function in_array;
use function is_dir;
use function is_file;
use function closedir;
use function opendir;
use function readdir;
use function realpath;

final class PathResolver
{
    /**
     * @var string[] $resolvedPaths
     */
    private $resolvedPaths = [];

    /**
     * @var string[] $failedPaths
     */
    private $failedPaths = [];

    /**
     * @param string[] $paths
     * @return string[]
     */
    public function resolvePaths(array $paths): array
    {
        foreach ($paths as $path) {
            $this->resolvePath($path);
        }

        $this->resolvedPaths = array_filter($this->resolvedPaths, is_file(...));

        return $this->resolvedPaths;
    }

    private function resolvePath(string $path): void
    {
        $realPath = realpath($path);
        if ($realPath === false) {
            $this->failedPaths[] = $path;
            return;
        }

        if (in_array($realPath, $this->resolvedPaths)) {
            return;
        }

        $this->resolvedPaths[] = $realPath;

        if (!is_dir($realPath)) {
            return;
        }

        $dirHandle = opendir($realPath);
        if ($dirHandle === false) {
            $this->failedPaths[] = $realPath;
            return;
        }

        while (($file = readdir($dirHandle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $this->resolvePath($realPath . DIRECTORY_SEPARATOR . $file);
        }

        closedir($dirHandle);
    }


    /**
     * @return string[]
     */
    public function getFailedPaths(): array
    {
        return $this->failedPaths;
    }
}
