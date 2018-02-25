<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain;

use PhpParser\NodeVisitor;
use Niktux\DDD\Analyzer\Dispatcher;

interface Visitor extends NodeVisitor
{
    public function setDispatcher(Dispatcher $dispatcher);

    public function startProject(): void;
    public function endProject(): void;
}
