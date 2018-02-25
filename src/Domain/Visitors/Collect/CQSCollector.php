<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Visitors\Collect;

use Niktux\DDD\Analyzer\Domain\ContextualVisitor;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Niktux\DDD\Analyzer\Domain\Services\NamespaceInterpreter;
use Niktux\DDD\Analyzer\Domain\Services\KnowledgeBase;
use Niktux\DDD\Analyzer\Domain\ValueObjects\CQS\Query;
use Niktux\DDD\Analyzer\Domain\ValueObjects\CQS\Command;

class CQSCollector extends ContextualVisitor
{
    private
        $interpreter,
        $base,
        $queryInterface,
        $commandInterface;

    public function __construct(KnowledgeBase $base, NamespaceInterpreter $interpreter, string $queryInterface, string $commandInterface)
    {
        parent::__construct();

        $this->interpreter = $interpreter;
        $this->base = $base;

        $this->queryInterface = $queryInterface;
        $this->commandInterface = $commandInterface;
    }

    public function startProject(): void
    {
        $this->queryInterface = $this->base->loadType($this->queryInterface);
        $this->commandInterface = $this->base->loadType($this->commandInterface);
    }

    protected function enter(Node $node): void
    {
        if($node instanceof Class_)
        {
            if($this->currentNamespace === null
            || $this->interpreter->canTranslate($this->currentNamespace) === false
            || $node->name === null
            || $node->isAbstract()
            )
            {
                return;
            }

            $fqn = $this->interpreter->translate($this->currentNamespace);
            $fqn = $fqn->concat($node->name);

            $type = $this->base->types()->get($fqn->fqn());

            if($type->isA($this->queryInterface))
            {
                $this->base->queries()->add(
                    new Query($fqn)
                );
            }

            if($type->isA($this->commandInterface))
            {
                $this->base->commands()->add(
                    new Command($fqn)
                );
            }
        }
    }
}
