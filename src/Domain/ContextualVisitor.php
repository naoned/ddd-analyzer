<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\ClassMethod;
use Niktux\DDD\Analyzer\Domain\ValueObjects\FullyQualifiedName;
use Niktux\DDD\Analyzer\Domain\ValueObjects\ObjectType;
use Niktux\DDD\Analyzer\Defect;

abstract class ContextualVisitor extends AbstractVisitor
{
    protected
        $nodeStack,
        $currentNamespace,
        $currentObjectType,
        $currentMethod;

    public function __construct()
    {
        parent::__construct();

        $this->nodeStack = new \SplStack();
    }

    final public function beforeTraverse(array $nodes)
    {
        $this->currentNamespace = null;
        $this->currentObjectType = null;
        $this->currentMethod = null;

        $this->before($nodes);
    }

    final public function enterNode(Node $node)
    {
        if($node instanceof Namespace_)
        {
            $this->currentNamespace = new FullyQualifiedName($node->name->toString());
        }
        elseif($node instanceof Class_)
        {
            $this->currentObjectType = new ObjectDefinition($this->currentNamespace, $node->name);
        }
        elseif($node instanceof Interface_)
        {
            $this->currentObjectType = new ObjectDefinition($this->currentNamespace, $node->name, ObjectType::interface());
        }
        elseif($node instanceof Trait_)
        {
            $this->currentObjectType = new ObjectDefinition($this->currentNamespace, $node->name, ObjectType::trait());
        }
        elseif($node instanceof ClassMethod)
        {
            $this->currentMethod = $node;
        }

        $this->enter($node);
        $this->nodeStack->push($node);
    }

    final public function leaveNode(Node $node)
    {
        if($node instanceof Namespace_)
        {
            $this->currentNamespace = null;
        }
        elseif($node instanceof Class_ || $node instanceof Interface_ || $node instanceof Trait_)
        {
            $this->currentObjectType = null;
        }
        elseif($node instanceof ClassMethod)
        {
            $this->currentMethod = null;
        }

        $this->leave($node);
        $this->nodeStack->pop();
    }

    final public function afterTraverse(array $nodes)
    {
        $this->after($nodes);
    }

    protected function dispatch(Defect $event): void
    {
        if($this->currentNamespace instanceof FullyQualifiedName)
        {
            $event->setNamespace($this->currentNamespace);
        }

        if($this->currentObjectType instanceof ObjectDefinition)
        {
            $event->setObject($this->currentObjectType);
        }

        parent::dispatch($event);
    }

    protected function before(array $nodes)
    {

    }

    protected function enter(Node $node)
    {

    }

    protected function leave(Node $node)
    {

    }

    protected function after(array $nodes)
    {

    }
}