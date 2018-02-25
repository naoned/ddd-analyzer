<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain;

use Niktux\DDD\Analyzer\Events\Defect;

class ContextualizedDefect implements \JsonSerializable
{
    private
        $defect,
        $file;

    public function __construct(Defect $defect, string $file)
    {
        $this->defect = $defect;
        $this->file = $file;
    }

    public function getDefect(): Defect
    {
        return $this->defect;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function jsonSerialize(): array
    {
        return $this->defect->jsonSerialize() + [
            'file' => $this->file,
            'line' => $this->defect->getLine(),
        ];
    }
}
