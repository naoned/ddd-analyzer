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
        $queries,
        $commands;

    public function __construct(KnowledgeBase $base, NamespaceInterpreter $interpreter)
    {
        parent::__construct();

        $this->interpreter = $interpreter;
        $this->queries = $base->queries();
        $this->commands = $base->commands();
    }

    protected function enter(Node $node): void
    {
        if($node instanceof Class_)
        {
            if($this->currentNamespace === null || $this->interpreter->canTranslate($this->currentNamespace) === false || $node->name === null)
            {
                return;
            }

            $fqn = $this->interpreter->translate($this->currentNamespace);
            $fqn = $fqn->concat($node->name);

            if(Query::isValid($fqn))
            {
                $this->queries->add(
                    new Query($fqn)
                );
            }
            elseif(Command::isValid($fqn))
            {
                $this->commands->add(
                    new Command($fqn)
                );
            }
        }
    }
}
