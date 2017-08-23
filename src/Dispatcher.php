<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer;

interface Dispatcher
{
    public function dispatch(Event $event);
}
