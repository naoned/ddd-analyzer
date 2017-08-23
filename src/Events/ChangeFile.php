<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Events;

use Niktux\DDD\Analyzer\Event;

class ChangeFile extends Event
{
    const
        EVENT_NAME = 'traverse.changeFile';

    private
        $currentFile;

    public function __construct($currentFile)
    {
        $this->currentFile = $currentFile;
    }

    public function getCurrentFile()
    {
        return $this->currentFile;
    }
}
