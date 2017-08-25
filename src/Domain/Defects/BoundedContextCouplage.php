<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Defects;

use Niktux\DDD\Analyzer\Defect;
use PhpParser\Node\Stmt\Use_;
use Niktux\DDD\Analyzer\Domain\ValueObjects\BoundedContext;
use Niktux\DDD\Analyzer\Domain\ValueObjects\InterpretedFQN;

final class BoundedContextCouplage extends Defect
{
    private
        $dependency,
        $bcFrom;

    public function __construct(Use_ $node, BoundedContext $bcFrom, InterpretedFQN $dependency)
    {
        parent::__construct($node);

        $this->dependency = $dependency;
        $this->bcFrom = $bcFrom;
    }

    public function getName(): string
    {
        return "Bounded contexts couplage";
    }

    public function getMessage(): string
    {
        return sprintf(
            "<type>Bounded context dependency</type> : <id>%s</id> depends on <id>%s</id> (use %s)",
            $this->bcFrom,
            $this->dependency->boundedContext(),
            $this->dependency->layer()->value() . '\\' . $this->dependency->relativeName()
        );
    }
}
