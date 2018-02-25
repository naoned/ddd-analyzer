<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Visitors;

use PhpParser\NodeVisitor;
use Niktux\DDD\Analyzer\Domain\AbstractVisitor;
use PhpParser\Node;

class PhpParserVisitorAdapter extends AbstractVisitor
{
    private
        $visitor;

    public function __construct(NodeVisitor $visitor)
    {
        parent::__construct();

        $this->visitor = $visitor;
    }

    public function beforeTraverse(array $nodes)
    {
        $this->visitor->beforeTraverse($nodes);
    }

    public function enterNode(Node $node)
    {
        $this->visitor->enterNode($node);
    }

    public function leaveNode(Node $node)
    {
        $this->visitor->leaveNode($node);
    }

    public function afterTraverse(array $nodes)
    {
        $this->visitor->afterTraverse($nodes);
    }
}
