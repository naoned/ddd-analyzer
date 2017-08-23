<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Events;

use Niktux\DDD\Analyzer\Event;

class TraverseEnd extends Event
{
    const
        EVENT_NAME = 'traverse.end';

    private
        $nth;

    public function __construct($nth = 1)
    {
        $this->nth = 1;

        if(is_int($nth))
        {
            $this->nth = $nth;
        }
    }

    public function getNth()
    {
        return $this->nth;
    }
}
