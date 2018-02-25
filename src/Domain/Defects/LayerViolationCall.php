<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Defects;

use PhpParser\Node\Stmt\Use_;
use Niktux\DDD\Analyzer\Events\Defect;
use Niktux\DDD\Analyzer\Domain\ValueObjects\InterpretedFQN;
use Niktux\DDD\Analyzer\Domain\ValueObjects\Layer;

final class LayerViolationCall extends Defect
{
    private
        $dependency,
        $layerFrom;

    public function __construct(Use_ $node, Layer $layerFrom, InterpretedFQN $dependency)
    {
        parent::__construct($node);

        $this->layerFrom = $layerFrom;
        $this->dependency = $dependency;
    }

    public function getName(): string
    {
        return "Layer violation";
    }

    public function getMessage(): string
    {
        return sprintf(
            "<type>Layer violation</type> in BC <bc>%s</bc> : <id>%s</id> calls <id>%s</id> (use %s)",
            $this->dependency->boundedContext(),
            $this->layerFrom,
            $this->dependency->layer(),
            $this->dependency->relativeName()
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => 'layer_violation',
            'bc' => (string) $this->dependency->boundedContext(),
            'from' => (string) $this->layerFrom,
            'to' => (string) $this->dependency->layer(),
            'callee' => (string) $this->dependency->relativeName()
        ];
    }
}
