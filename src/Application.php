<?php

declare(strict_types=1);

namespace Oru\Spec262;

use Oru\Spec262\Visitors\FunctionVisitor;
use Oru\Spec262\Visitors\MethodVisitor;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
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

use function file_get_contents;
use function ini_set;

#[AsCommand(
    name: '',
    description: 'Checks the provided source file or directory against the configured ECMAScript specification',
)]
final class Application extends SingleCommandApplication
{
    public function __construct(
        private Parser $parser,
        private NodeTraverser $traverser,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::IS_ARRAY, 'Source file or directory to check');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('xdebug.max_nesting_level', 3000);

        $bufferedOutput = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $io             = new SymfonyStyle($input, $output);
        $bufferedIo     = new SymfonyStyle($input, $bufferedOutput);
        $pathResolver   = new PathResolver();

        /**
         * @var string[] $paths
         */
        $paths = $input->getArgument('path');
        $paths = $pathResolver->resolvePaths($paths);

        $io->title('ECMAScript Specification Check');

        $io->newLine();
        $io->progressStart(count($paths));

        foreach ($paths as $filePath) {
            $this->checkFile($filePath, $bufferedIo);
            $io->progressAdvance(1);
        }

        $io->progressFinish();

        $bufferedText = $bufferedOutput->fetch();
        if ($bufferedText === '') {
            $io->success('No errors!');

            return Command::SUCCESS;
        }

        $io->error('Errors:');
        $io->write($bufferedText);

        return Command::FAILURE;
    }

    private function checkFile(string $filePath, StyleInterface&OutputInterface $io): void
    {
        $functionVisitor = new FunctionVisitor($io, $filePath);
        $methodVisitor   = new MethodVisitor($io, $filePath);
        $this->traverser->addVisitor($functionVisitor);
        $this->traverser->addVisitor($methodVisitor);

        try {
            if ($stmts = $this->parser->parse(file_get_contents($filePath))) {
                $this->traverser->traverse($stmts);
            }
        } catch (Throwable $e) {
            $io->error('Parse Error: ', $e->getMessage());
        } finally {
            $this->traverser->removeVisitor($functionVisitor);
            $this->traverser->removeVisitor($methodVisitor);
        }
    }
}
