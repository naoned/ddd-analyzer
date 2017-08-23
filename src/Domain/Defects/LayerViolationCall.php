<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Defects;

use Niktux\DDD\Analyzer\Defect;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;

final class LayerViolationCall extends Defect
{
    private
        $dependency,
        $bc,
        $layerFrom,
        $layerTo;

    public function __construct(Use_ $node, Name $dependency, string $bc, string $layerFrom, string $layerTo)
    {
        parent::__construct($node);

        $this->dependency = $dependency;
        $this->bc = $bc;
        $this->layerFrom = $layerFrom;
        $this->layerTo = $layerTo;
    }

    public function getMessage(): string
    {
        return sprintf(
            "<type>Layer violation</type> in BC <bc>%s</bc> : <id>%s</id> calls <id>%s</id> (use %s)",
            $this->bc,
            $this->layerFrom,
            $this->layerTo,
            $this->dependency->slice(4)->toString()
        );
    }
}
