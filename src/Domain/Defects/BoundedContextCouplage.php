<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Defects;

use Niktux\DDD\Analyzer\Defect;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;

final class BoundedContextCouplage extends Defect
{
    private
        $dependency,
        $bcFrom,
        $bcTo;

    public function __construct(Use_ $node, Name $dependency, string $bcFrom, string $bcTo)
    {
        parent::__construct($node);

        $this->dependency = $dependency;
        $this->bcFrom = $bcFrom;
        $this->bcTo = $bcTo;
    }

    public function getMessage(): string
    {
        return sprintf(
            "<type>Bounded context dependency</type> : <id>%s</id> depends on <id>%s</id> (use %s)",
            $this->bcFrom,
            $this->bcTo,
            $this->dependency->slice(3)->toString()
        );
    }
}
