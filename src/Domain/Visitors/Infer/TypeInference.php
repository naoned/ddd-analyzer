<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Visitors\Infer;

use PhpParser\Node;
use Niktux\DDD\Analyzer\Domain\Services\KnowledgeBase;
use Niktux\DDD\Analyzer\Domain\ContextualVisitor;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use Niktux\DDD\Analyzer\Domain\ValueObjects\FullyQualifiedName;
use Niktux\DDD\Analyzer\Domain\Type;
use PhpParser\Node\Name\FullyQualified;

class TypeInference extends ContextualVisitor
{
    private
        $types,
        $explored;

    public function __construct(KnowledgeBase $base)
    {
        parent::__construct();

        $this->types = $base->types();
        $this->explored = [];
    }

    protected function enter(Node $node): void
    {
        if($node instanceof Class_ || $node instanceof Interface_)
        {
            if($node->name === null)
            {
                return;
            }

            $fqn = $this->currentNamespace->concat($node->name);
            $type = $this->type($fqn);

            if($node->extends !== null)
            {
                $extends = $node->extends instanceof iterable ? $node->extends : [$node->extends];
                $this->deepInto($extends, $type);
            }

            if($node instanceof Class_ && $node->implements !== null)
            {
                $this->deepInto($node->implements, $type);
            }
        }
    }

    private function deepInto(iterable $types, Type $type): void
    {
        foreach($types as $otherType)
        {
            if($otherType instanceof FullyQualified)
            {
                $type->addType(
                    $this->type($otherType)
                );
            }
        }
    }

    /**
     * @param FullyQualified|FullyQualifiedName fqn
     */
    private function type($fqn): Type
    {
        if($fqn instanceof FullyQualified)
        {
            $fqn = FullyQualifiedName::fromPhpParserFQN($fqn);
        }

        $type = $this->types->get($fqn);

        if($type === null)
        {
            $type = new Type($fqn);
            $this->types->add($type);
        }

        return $type;
    }

    protected function after(array $nodes): void
    {
    }

    public function endProject(): void
    {
        foreach($this->types as $type)
        {
            $this->exploreType($type);
        }
    }

    private function exploreType(Type $type): void
    {
        if(isset($this->explored[(string) $type]))
        {
            return;
        }

        $this->explored[(string) $type] = true;

        foreach($type->others() as $other)
        {
            $this->exploreType($other);
        }

        foreach($this->recursiveOthers($type) as $other)
        {
            $type->addType($other);
        }
    }

    private function recursiveOthers(Type $type): array
    {
        // Vital optimization
        if($type->resolved === true)
        {
            return [$type] + $type->others();
        }

        $result = [];

        foreach($type->others() as $other)
        {
            $result += $this->recursiveOthers($other);
        }

        foreach($result as $resultEntry)
        {
            $type->addType($resultEntry);
        }
        $type->resolved = true;

        return [$type] + $result;
    }
}
