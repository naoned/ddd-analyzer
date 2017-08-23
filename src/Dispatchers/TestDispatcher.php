<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Dispatchers;

use Niktux\DDD\Analyzer\Dispatcher;
use Niktux\DDD\Analyzer\Event;

class TestDispatcher implements Dispatcher
{
    private
        $events;

    public function __construct()
    {
        $this->events = array();
    }

    public function dispatch(Event $event)
    {
        $this->events[] = $event;
    }

    public function getEvents()
    {
        return $this->events;
    }
}
