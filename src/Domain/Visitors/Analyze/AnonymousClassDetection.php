<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Visitors\Analyze;

use PhpParser\Node;
use Niktux\DDD\Analyzer\Domain\ContextualVisitor;
use PhpParser\Node\Stmt\Class_;
use Niktux\DDD\Analyzer\Domain\Defects\AnonymousClass;

class AnonymousClassDetection extends ContextualVisitor
{
    public function enter(Node $node): void
    {
        if($node instanceof Class_)
        {
            if($node->isAnonymous())
            {
                $this->dispatch(new AnonymousClass($node));
            }
        }
    }
}
