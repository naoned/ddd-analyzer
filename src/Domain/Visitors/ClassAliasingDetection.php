<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Niktux\DDD\Analyzer\Domain\ContextualVisitor;
use Niktux\DDD\Analyzer\Domain\Defects\ClassAliasing;

class ClassAliasingDetection extends ContextualVisitor
{
    private
        $aliasWhitelist;

    public function __construct(array $aliasWhitelist = [])
    {
        parent::__construct();

        $this->aliasWhitelist = $aliasWhitelist;
    }

    public function enter(Node $node)
    {
        if($node instanceof Use_)
        {
            if($node->type === Use_::TYPE_NORMAL)
            {
                foreach($node->uses as $use)
                {
                    $name = $use->name->getLast();
                    $alias = $use->alias;

                    if($alias !== $name)
                    {
                        $this->aliasFound($use);
                    }
                }
            }
        }
    }

    private function aliasFound(UseUse $node): void
    {
        if(in_array($node->alias, $this->aliasWhitelist))
        {
            return;
        }

        $this->dispatch(new ClassAliasing($node));
    }
}
