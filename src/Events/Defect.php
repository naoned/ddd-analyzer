<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Events;

use PhpParser\Node;
use Niktux\DDD\Analyzer\Domain\ObjectDefinition;
use Niktux\DDD\Analyzer\Domain\ValueObjects\FullyQualifiedName;
use Niktux\DDD\Analyzer\Event;

abstract class Defect extends Event implements \JsonSerializable
{
    const
        EVENT_NAME = 'defect';

    protected
        $object,
        $namespace,
        $node,
        $context;

    public function __construct(Node $node)
    {
        $this->object = null;
        $this->namespace = null;
        $this->node = $node;
        $this->context = null;
    }

    public function getLine(): int
    {
        return $this->node->getLine();
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function getContext(): Node
    {
        if($this->context instanceof Node)
        {
            return $this->context;
        }

        return $this->node;
    }

    public function setContext(Node $contextNode): self
    {
        $this->context = $contextNode;

        return $this;
    }

    public function getObject(): ?ObjectDefinition
    {
        return $this->object;
    }

    public function setObject(ObjectDefinition $object): self
    {
        $this->object = $object;

        return $this;
    }

    public function getNamespace(): ?FullyQualifiedName
    {
        return $this->namespace;
    }

    public function setNamespace(FullyQualifiedName $fqn): self
    {
        $this->namespace = $fqn;

        return $this;
    }

    abstract public function getMessage(): string;
    abstract public function getName(): string;
}