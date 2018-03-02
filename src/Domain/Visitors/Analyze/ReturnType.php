<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Visitors\Analyze;

use PhpParser\Node;
use Niktux\DDD\Analyzer\Domain\ContextualVisitor;
use Puzzle\Configuration;
use PhpParser\Node\Stmt\ClassMethod;
use Niktux\DDD\Analyzer\Domain\Defects\MissingReturnType;
use Niktux\DDD\Analyzer\Domain\Services\KnowledgeBase;
use Niktux\DDD\Analyzer\Domain\ValueObjects\FullyQualifiedName;
use Niktux\DDD\Analyzer\Domain\Type;

class ReturnType extends ContextualVisitor
{
    private
        $methodWhitelist,
        $typeWhitelist,
        $base;

    public function __construct(Configuration $configuration, KnowledgeBase $base)
    {
        parent::__construct();

        $this->methodWhitelist = $configuration->read('whitelists/methods', []);
        $this->typeWhitelist = $configuration->read('whitelists/types', []);
        $this->base = $base;
    }

    public function startProject(): void
    {
        $this->typeWhitelist = $this->loadTypeWhitelist($this->typeWhitelist);
    }

    private function loadTypeWhitelist(array $fqnAsStrings)
    {
        $list = [];

        foreach($fqnAsStrings as $name)
        {
            $list[] = new Type(
                new FullyQualifiedName($name)
            );
        }

        return $list;
    }

    public function enter(Node $node): void
    {
        if($node instanceof ClassMethod)
        {
            if($node->returnType !== null)
            {
                return;
            }

            if(in_array($node->name, $this->methodWhitelist))
            {
                return;
            }

            $type = $this->type();
            foreach($this->typeWhitelist as $skipType)
            {
                if($type->isA($skipType))
                {
                    return;
                }
            }

            $this->dispatch(new MissingReturnType($node, $this->currentObjectType));
        }
    }

    private function type(): Type
    {
        return $this->base->types()->get(
            $this->currentObjectType->fqn(),
            $this->currentObjectType->type()
        );
    }
}
