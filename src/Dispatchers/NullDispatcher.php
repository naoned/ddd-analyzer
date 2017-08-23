<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Dispatchers;

use Niktux\DDD\Analyzer\Dispatcher;
use Niktux\DDD\Analyzer\Event;

class NullDispatcher implements Dispatcher
{
    public function dispatch(Event $event)
    {
    }
}
