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

class Analyzer implements VisitableAnalyzer
{
    private
        $visitors,
        $nodeTraversers,
        $dispatcher,
        $fs;

    public function __construct(Dispatcher $dispatcher, Filesystem $fs)
    {
        $this->visitors = [];

        $this->nodeTraversers = array(
            (string) TraverseMode::preAnalyze() => new NodeTraverser(),
            (string) TraverseMode::analyze() => new NodeTraverser(),
        );

        $this->dispatcher = $dispatcher;
        $this->fs = $fs;
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
        $nodes = $this->parseFiles();

        $this->preAnalyze($nodes);
        $this->analyze($nodes);

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

        foreach($iterator as $key)
        {
            if($adapter->isDirectory($key) === false)
            {
                $nodes[$key] = $this->parseFile($this->fs->get($key));
            }
        }

        return $nodes;
    }

    private function parseFile(File $file): ?iterable
    {
        $parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);

        return $parser->parse($file->getContent());
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
        foreach($nodes as $file => $stmts)
        {
            $this->dispatcher->dispatch(new ChangeFile($file));
            $traverser->traverse($stmts);
        }
    }
}
