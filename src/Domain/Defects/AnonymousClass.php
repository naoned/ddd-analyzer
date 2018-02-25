<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Defects;

use Niktux\DDD\Analyzer\Events\Defect;

final class AnonymousClass extends Defect
{
    public function getName(): string
    {
        return "Anonymous class";
    }

    private function extends() : ?string
    {
        $extends = null;

        if($this->node->extends !== null)
        {
            $extends = $this->node->extends->getLast();
        }

        return $extends;
    }

    public function getMessage(): string
    {
        $extends = $this->extends();

        // TODO implements

        return sprintf(
            "<type>Anonymous class detected</type>%s",
            $extends ? " <-- <id>$extends</id>" : ""
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => 'anonymous_class',
            'extends' => $this->extends(),
        ];
    }
}
