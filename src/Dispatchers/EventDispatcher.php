<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Dispatchers;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Niktux\DDD\Analyzer\Dispatcher;
use Niktux\DDD\Analyzer\Event;

class EventDispatcher implements Dispatcher
{
    private
        $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function dispatch(Event $event)
    {
        $this->dispatcher->dispatch($event->getEventName(), $event);
    }
}
