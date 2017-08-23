<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Defects;

use Niktux\DDD\Analyzer\Defect;

final class AnonymousClass extends Defect
{
    public function getMessage(): string
    {
        $extends = '';

        if($this->node->extends !== null)
        {
            $extends = $this->node->extends->getLast();
        }

        // TODO implements

        return sprintf(
            "<type>Anonymous class detected</type>%s",
            $extends ? " <-- <id>$extends</id>" : ""
        );
    }
}
