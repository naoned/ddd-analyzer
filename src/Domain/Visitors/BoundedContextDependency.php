<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use Niktux\DDD\Analyzer\Domain\ContextualVisitor;
use PhpParser\Node\Name;
use Niktux\DDD\Analyzer\Domain\Defects\BoundedContextCouplage;
use Niktux\DDD\Analyzer\Domain\Defects\LayerViolationCall;
use Niktux\DDD\Analyzer\Domain\NamespaceInterpreter;
use Niktux\DDD\Analyzer\Domain\ValueObjects\FullyQualifiedName;
use Niktux\DDD\Analyzer\Domain\ValueObjects\InterpretedFQN;

class BoundedContextDependency extends ContextualVisitor
{
    private
        $interpreter;

    public function __construct(NamespaceInterpreter $interpreter)
    {
        parent::__construct();

        $this->interpreter = $interpreter;
    }

    public function enter(Node $node)
    {
        if($node instanceof Use_)
        {
            if($this->interpreter->canTranslate($this->currentNamespace) === false)
            {
                return;
            }

            foreach($node->uses as $use)
            {
                $dependencyFqn = $this->analyze($use->name);

                if($dependencyFqn !== null)
                {
                    $this->checkRule($node, $dependencyFqn);
                }
            }
        }
    }

    private function analyze(Name $node): ?InterpretedFQN
    {
        $fqn = new FullyQualifiedName($node->toString());

        if($this->interpreter->canTranslate($fqn))
        {
            $fqn = $this->interpreter->translate($fqn);
            $bc = $fqn->boundedContext();

            $notBC = ['Console', 'Controllers', 'Domain', 'Persistence', 'Projection', 'Services', 'Workers'];

            if(! in_array($bc->value(), $notBC))
            {
                return $fqn;
            }
        }

        return null;
    }

    private function checkRule(Use_ $node, InterpretedFQN $dependency)
    {
        $namespace = $this->interpreter->translate($this->currentNamespace);

        if(! $namespace->boundedContext()->equals($dependency->boundedContext()))
        {
            $this->dispatch(new BoundedContextCouplage($node, $namespace->boundedContext(), $dependency));
        }

        if($namespace->layer() !== $dependency->layer())
        {
            if($namespace->layer()->isDeeperThan($dependency->layer()))
            {
                $this->dispatch(new LayerViolationCall($node, $namespace->layer(), $dependency));
            }
        }
    }
}