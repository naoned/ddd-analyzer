<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Visitors\RawCollect;

use PhpParser\Node;
use Niktux\DDD\Analyzer\Domain\Services\KnowledgeBase;
use Niktux\DDD\Analyzer\Domain\ContextualVisitor;
use Niktux\DDD\Analyzer\Domain\Type;
use PhpParser\Node\Stmt\ClassLike;
use Niktux\DDD\Analyzer\Domain\ValueObjects\ObjectType;

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
        if($node instanceof ClassLike)
        {
            if($node->name === null)
            {
                return;
            }

            $fqn = $this->currentNamespace->concat($node->name);

            if(! $this->types->has($fqn))
            {
                $type = new Type($fqn, ObjectType::fromNode($node));
                $this->types->add($type);
            }
        }
    }
}
