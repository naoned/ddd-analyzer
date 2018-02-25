<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain;

use Niktux\DDD\Analyzer\Dispatchers\NullDispatcher;
use Niktux\DDD\Analyzer\Dispatcher;
use Niktux\DDD\Analyzer\Events\Defect;
use PhpParser\NodeVisitorAbstract;

abstract class AbstractVisitor extends NodeVisitorAbstract implements Visitor
{
    protected
        $dispatcher;

    public function __construct()
    {
        $this->dispatcher = new NullDispatcher();
    }

    public function setDispatcher(Dispatcher $dispatcher): self
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    protected function dispatch(Defect $event): void
    {
        $this->dispatcher->dispatch($event);
    }

    public function startProject(): void
    {
    }

    public function endProject(): void
    {
    }
}
