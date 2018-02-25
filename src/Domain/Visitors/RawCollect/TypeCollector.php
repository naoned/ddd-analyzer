<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Visitors\RawCollect;

use PhpParser\Node;
use Niktux\DDD\Analyzer\Domain\Services\KnowledgeBase;
use Niktux\DDD\Analyzer\Domain\ContextualVisitor;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use Niktux\DDD\Analyzer\Domain\Type;

class TypeCollector extends ContextualVisitor
{
    private
        $types;

    public function __construct(KnowledgeBase $base)
    {
        parent::__construct();

        $this->types = $base->types();
    }

    protected function enter(Node $node): void
    {
        if($node instanceof Class_ || $node instanceof Interface_)
        {
            if($node->name === null)
            {
                return;
            }

            $fqn = $this->currentNamespace->concat($node->name);

            if(! $this->types->has($fqn))
            {
                $type = new Type($fqn);
                $this->types->add($type);
            }
        }
    }
}
