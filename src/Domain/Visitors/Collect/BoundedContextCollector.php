<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Visitors\Collect;

use Niktux\DDD\Analyzer\Domain\ContextualVisitor;
use PhpParser\Node;
use Niktux\DDD\Analyzer\Domain\Services\NamespaceInterpreter;
use Niktux\DDD\Analyzer\Domain\Services\KnowledgeBase;
use PhpParser\Node\Stmt\ClassLike;

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
        if($node instanceof ClassLike)
        {
            if(! $this->inNamespace())
            {
                return;
            }

            $name = $node->name;

            if($name === null)
            {
                $name = "{anonymous}";
            }

            $fqn = $this->currentNamespace->concat((string) $name);

            if($this->interpreter->canTranslate($fqn) === false)
            {
                return;
            }

            $fqn = $this->interpreter->translate($fqn);
            $this->boundedContexts->add($fqn->boundedContext());
        }
    }
}
