<?php

declare(strict_types=1);

namespace Oru\Spec262;

use Oru\Spec262\Exceptions\PathException;
use Oru\Spec262\Visitors\FunctionVisitor;
use Oru\Spec262\Visitors\MethodVisitor;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

use function assert;
use function file_get_contents;
use function ini_set;
use function is_dir;
use function is_file;
use function is_link;
use function iterator_to_array;
use function readlink;
use function realpath;
use function sprintf;

#[AsCommand(
    name: 'check',
    description: 'Checks the provided source file or directory against the configured ECMAScript specification',
)]
final class Application extends SingleCommandApplication
{
    private Parser $parser;

    private NodeTraverser $traverser;

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function __construct(?string $name = null)
    {
        parent::__construct($name);

        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        $this->traverser = new NodeTraverser();
    }

    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::REQUIRED, 'Source file or directory to check');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('xdebug.max_nesting_level', 3000);

        $bufferedOutput = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $io             = new SymfonyStyle($input, $output);
        $bufferedIo     = new SymfonyStyle($input, $bufferedOutput);

        $path = (string) $input->getArgument('path');

        if (is_link($path)) {
            $path = readlink($path);

            assert($path !== false);
        }

        $path = realpath($path)
            ?: throw PathException::noCanonicalizedAbsolutePathName((string) $input->getArgument('path'));

        $isDirectory = is_dir($path);

        $io->title('ECMAScript Specification Check');

        /**
         * @var iterable<string[]> $progressIterable
         */
        $progressIterable = $io->progressIterate($this->recursivelyFindAllPHPFilesInDirectory($path));

        $io->text(sprintf('Checking %s `%s`', $isDirectory ? 'directory' : 'file', $path));
        $io->newLine();

        foreach ($progressIterable as [$filePath]) {
            $this->checkFile($filePath, $bufferedIo);
        }

        $bufferedText = $bufferedOutput->fetch();
        if ($bufferedText === '') {
            $io->success('No errors!');
        } else {
            $io->error('Errors:');
        }

        $io->write($bufferedText);

        return Command::SUCCESS;
    }

    /**
     * @return string[][]
     */
    private function recursivelyFindAllPHPFilesInDirectory(string $path): array
    {
        if (!is_dir($path) && is_file($path)) {
            return [[$path]];
        }

        $regex = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path)
            ),
            '/^.+\.php$/i',
            RecursiveRegexIterator::GET_MATCH
        );

        /**
         * @var string[][]
         */
        return iterator_to_array($regex);
    }

    private function checkFile(string $filePath, StyleInterface&OutputInterface $io): void
    {
        $functionVisitor = new FunctionVisitor($io, $filePath);
        $methodVisitor = new MethodVisitor($io, $filePath);
        $this->traverser->addVisitor($functionVisitor);
        $this->traverser->addVisitor($methodVisitor);

        try {
            if ($stmts = $this->parser->parse(file_get_contents($filePath))) {
                $this->traverser->traverse($stmts);
            }
        } catch (Throwable $e) {
            echo 'Parse Error: ', $e->getMessage();
        } finally {
            $this->traverser->removeVisitor($functionVisitor);
            $this->traverser->removeVisitor($methodVisitor);
        }
    }
}