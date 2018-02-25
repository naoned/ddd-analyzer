<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain;

use Niktux\DDD\Analyzer\Domain\ValueObjects\TraverseMode;

interface VisitableAnalyzer
{
    public function addVisitor(TraverseMode $mode, Visitor $visitor): void;
    public function addVisitors(TraverseMode $mode, array $visitors): void;
}
