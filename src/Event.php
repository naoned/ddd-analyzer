<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer;

abstract class Event extends \Symfony\Component\EventDispatcher\Event
{
    const
        EVENT_NAME = 'generic.event';

    public function getEventName()
    {
        return static::EVENT_NAME;
    }
}
