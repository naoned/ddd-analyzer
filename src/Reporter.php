<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer;

use Niktux\DDD\Analyzer\Domain\Collections\SortedDefectCollection;

interface Reporter
{
    public function render(SortedDefectCollection $defects): self;

    public function save(string $reportFilename): void;
}
