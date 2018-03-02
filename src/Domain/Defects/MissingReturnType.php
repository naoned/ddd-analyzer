<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Defects;

use Niktux\DDD\Analyzer\Events\Defect;
use PhpParser\Node\Stmt\ClassMethod;
use Niktux\DDD\Analyzer\Domain\ObjectDefinition;

final class MissingReturnType extends Defect
{
    private
        $type,
        $method;

    public function __construct(ClassMethod $node, ObjectDefinition $type)
    {
        parent::__construct($node);

        $this->type = $type;
        $this->method = $node->name;
    }

    public function getName(): string
    {
        return "Missing return type";
    }

    public function getMessage(): string
    {
        return sprintf(
            "<type>Missing return type</type> in %s::%s",
            $this->type->fullname(),
            $this->method
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => 'missing return type',
            'context' => $this->type->fullname(),
            'method' => $this->method,
        ];
    }
}
