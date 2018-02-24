<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain;

use Gaufrette\Filesystem;
use Gaufrette\File;
use PhpParser\Parser;
use PhpParser\NodeTraverser;
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
        PROGRESS_BAR_FORMAT = 'very_verbose';

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
            (string) TraverseMode::preAnalyze() => new NodeTraverser(),
            (string) TraverseMode::analyze() => new NodeTraverser(),
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

    public function addVisitor(TraverseMode $mode, Visitor $visitor): self
    {
        $mode = $mode->value();

        if(! isset($this->nodeTraversers[$mode]))
        {
            throw new \RuntimeException("$mode is not a valid traverse mode");
        }

        $visitor->setDispatcher($this->dispatcher);
        $this->nodeTraversers[$mode]->addVisitor($visitor);
        $this->visitors[] = $visitor;

        return $this;
    }

    public function run(): void
    {
        $this->displayTitle('DDD Analyzer');

        $this->displayStep("Parsing files");
        $nodes = $this->parseFiles();

        $this->displayStep("Pre-analyzing");
        $this->preAnalyze($nodes);

        $this->displayStep("Analyzing");
        $this->analyze($nodes);

        $this->displayStep("Creating reports");
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

    private function preAnalyze(array $nodes): void
    {
        $this->traverse($nodes, $this->nodeTraversers[(string) TraverseMode::preAnalyze()]);
    }

    private function analyze(array $nodes): void
    {
        $this->traverse($nodes, $this->nodeTraversers[(string) TraverseMode::analyze()]);
    }

    private function traverse(array $nodes, NodeTraverser $traverser)
    {
        $bar = $this->startProgressBar(count($nodes));

        foreach($nodes as $file => $stmts)
        {
            $bar->setMessage(explode('/',$file)[0], 'file');
            $this->dispatcher->dispatch(new ChangeFile($file));
            $traverser->traverse($stmts);
            $bar->advance();
        }

        $this->finishProgressBar($bar);
    }

    private function startProgressBar(int $count): ProgressBar
    {
        /*
        $bar = new ProgressBar($this->output, $count);
        $bar->setFormat(self::PROGRESS_BAR_FORMAT);
        $progress->setRedrawFrequency(10);
        $bar->start();
        //*/

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
        $nbSteps = 4;

        $this->output->writeln([
            '',
            sprintf('<fg=yellow;options=bold>%d/%d %s</>', ++$count, $nbSteps, $description),
            '',
        ]);
    }
}
