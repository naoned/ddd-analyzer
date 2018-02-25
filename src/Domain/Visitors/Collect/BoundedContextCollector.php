<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Visitors\Collect;

use Niktux\DDD\Analyzer\Domain\ContextualVisitor;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Niktux\DDD\Analyzer\Domain\Services\NamespaceInterpreter;
use Niktux\DDD\Analyzer\Domain\Services\KnowledgeBase;

class BoundedContextCollector extends ContextualVisitor
{
    private
        $interpreter,
        $boundedContexts;

    public function __construct(KnowledgeBase $base, NamespaceInterpreter $interpreter)
    {
        parent::__construct();

        $this->interpreter = $interpreter;
        $this->boundedContexts = $base->boundedContexts();
    }

    protected function enter(Node $node): void
    {
        if($node instanceof Class_)
        {
            if($this->currentNamespace === null || $this->interpreter->canTranslate($this->currentNamespace) === false)
            {
                return;
            }

            $fqn = $this->interpreter->translate($this->currentNamespace);
            $this->boundedContexts->add($fqn->boundedContext());
        }
    }
}
