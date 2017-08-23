<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Defects;

use Niktux\DDD\Analyzer\Defect;

class ClassAliasing extends Defect
{
    public function getMessage(): string
    {
        return sprintf(
            "<type>Disallowed class aliasing</type> : <id>%s</id> --> <id>%s</id>",
            $this->node->name->getLast(),
            $this->node->alias
        );
    }
}
