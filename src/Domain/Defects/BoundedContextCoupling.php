<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Defects;

use PhpParser\Node\Stmt\Use_;
use Niktux\DDD\Analyzer\Events\Defect;
use Niktux\DDD\Analyzer\Domain\ValueObjects\BoundedContext;
use Niktux\DDD\Analyzer\Domain\ValueObjects\InterpretedFQN;

final class BoundedContextCoupling extends Defect
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
        return "Bounded contexts coupling";
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

    public function jsonSerialize(): array
    {
        return [
            'type' => 'bc_coupling',
            'from' => (string)  $this->bcFrom,
            'to' => (string) $this->dependency->boundedContext(),
            'dependency' => $this->dependency->layer()->value() . '\\' . $this->dependency->relativeName()
        ];
    }
}
