<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain;

use PhpParser\NodeTraverser;

class ProjectTraverser extends NodeTraverser
{
    public function endProject(): void
    {
        foreach($this->visitors as $visitor)
        {
            if($visitor instanceof Visitor)
            {
                $visitor->endProject();
            }
        }
    }
}
