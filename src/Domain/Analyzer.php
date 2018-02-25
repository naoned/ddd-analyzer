<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain;

use Gaufrette\Filesystem;
use Gaufrette\File;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Niktux\DDD\Analyzer\Domain\ValueObjects\TraverseMode;
use Niktux\DDD\Analyzer\Dispatcher;
use Niktux\DDD\Analyzer\Events\TraverseEnd;
use Niktux\DDD\Analyzer\Events\ChangeFile;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Helper\ProgressBar;

class Analyzer implements VisitableAnalyzer
{
    private const
        PROGRESS_BAR_FORMAT = 'very_verbose',
        NB_STEPS = 7;

    private
        $output,
        $skipTests,
        $visitors,
        $nodeTraversers,
        $dispatcher,
        $fs;

    public function __construct(Dispatcher $dispatcher, Filesystem $fs)
    {
        $this->output = new NullOutput();
        $this->skipTests = false;
        $this->visitors = [];

        $this->nodeTraversers = array(
            (string) TraverseMode::complete() => new ProjectTraverser(),
            (string) TraverseMode::rawCollect() => new ProjectTraverser(),
            (string) TraverseMode::infer() => new ProjectTraverser(),
            (string) TraverseMode::collect() => new ProjectTraverser(),
            (string) TraverseMode::analyze() => new ProjectTraverser(),
        );

        $this->dispatcher = $dispatcher;
        $this->fs = $fs;
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function skipTests(): void
    {
        $this->skipTests = true;
    }

    public function addVisitors(TraverseMode $mode, array $visitors): void
    {
        foreach($visitors as $visitor)
        {
            $this->addVisitor($mode, $visitor);
        }
    }

    public function addVisitor(TraverseMode $mode, Visitor $visitor): void
    {
        $mode = $mode->value();

        if(! isset($this->nodeTraversers[$mode]))
        {
            throw new \RuntimeException("$mode is not a valid traverse mode");
        }

        $visitor->setDispatcher($this->dispatcher);
        $this->nodeTraversers[$mode]->addVisitor($visitor);
        $this->visitors[] = $visitor;
    }

    public function run(): void
    {
        $this->displayTitle('DDD Analyzer');

        $this->displayStep("Parse files");
        $nodes = $this->parseFiles();

        $this->displayStep("Analyze pass #1 [complete AST]");
        $this->pass(TraverseMode::complete(), $nodes);

        $this->displayStep("Analyze pass #2 [raw data collection]");
        $this->pass(TraverseMode::rawCollect(), $nodes);

        $this->displayStep("Analyze pass #3 [infer]");
        $this->pass(TraverseMode::infer(), $nodes);

        $this->displayStep("Analyze pass #4 [inferred data collection]");
        $this->pass(TraverseMode::collect(), $nodes);

        $this->displayStep("Analyze pass #5 [analyze]");
        $this->pass(TraverseMode::analyze(), $nodes);

        $this->displayStep("Create reports");
        $this->dispatcher->dispatch(new TraverseEnd());
    }

    private function parseFiles(): iterable
    {
        $nodes = array();

        $adapter = $this->fs->getAdapter();

        $iterator = new \RegexIterator(
            new \ArrayIterator($this->fs->keys()),
            '~.php$~'
        );

        if($this->skipTests === true)
        {
            $iterator = new \CallbackFilterIterator($iterator, function($file) {
                return preg_match('~/tests/~', $file) === 0;
            });
        }

        $bar = $this->startProgressBar(iterator_count($iterator));

        foreach($iterator as $key)
        {
            if($adapter->isDirectory($key) === false)
            {
                $bar->setMessage(explode('/',$key)[0], 'file');
                $nodes[$key] = $this->parseFile($this->fs->get($key));
            }

            $bar->advance();
        }

        $this->finishProgressBar($bar);

        return $nodes;
    }

    private function parseFile(File $file): ?iterable
    {
        try
        {
            $parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);

            return $parser->parse($file->getContent());
        }
        catch(\Exception $e)
        {
            throw new \RuntimeException(sprint(
                "Exception %s while parsing file %s : %s",
                get_class($e),
                $file->getName(),
                $e->getMessage()
            ));
        }
    }

    private function pass(TraverseMode $mode, array $nodes): void
    {
        $this->traverse($nodes, $this->nodeTraversers[(string) $mode]);
    }

    private function traverse(array $nodes, ProjectTraverser $traverser)
    {
        $bar = $this->startProgressBar(count($nodes));

        $traverser->startProject();

        foreach($nodes as $file => $stmts)
        {
            $bar->setMessage(explode('/',$file)[0], 'file');

            $this->dispatcher->dispatch(new ChangeFile($file));
            $traverser->traverse($stmts);

            $bar->advance();
        }

        $traverser->endProject();

        $this->finishProgressBar($bar);
    }

    private function startProgressBar(int $count): ProgressBar
    {
        $bar = new ProgressBar($this->output, $count);
        $bar->setFormat(" \033[44;37m %file:-37s% \033[0m\n %current%/%max% %bar% %percent:3s%%\n ETA  %remaining:-10s%");
        $bar->setBarCharacter($done = "\033[32m●\033[0m");
        $bar->setEmptyBarCharacter($empty = "\033[31m●\033[0m");
        $bar->setProgressCharacter($progress = "\033[97m➤\033[0m");
        $bar->setMessage('', 'file');
        $bar->setRedrawFrequency(10);
        $bar->start();

        return $bar;
    }

    private function finishProgressBar(ProgressBar $bar): void
    {
        $bar->finish();
        $this->output->writeln('');
    }

    private function displayTitle(string $title): void
    {
        $this->output->writeln([
            '<fg=yellow;options=bold>' . str_repeat('-', 40),
            $title,
            str_repeat('-', 40). '</>',
        ]);
    }

    private function displayStep(string $description): void
    {
        static $count = 0;
        $nbSteps = self::NB_STEPS;

        $this->output->writeln([
            '',
            sprintf('<fg=yellow;options=bold>%d/%d %s</>', ++$count, $nbSteps, $description),
            '',
        ]);
    }
}
