<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Collections\CQS;

use Niktux\DDD\Analyzer\Domain\ValueObjects\CQS\Command;

class CommandCollection implements \IteratorAggregate, \Countable
{
    private
        $commands;

    public function __construct(iterable $commands = [])
    {
        $this->commands = [];

        foreach($commands as $command)
        {
            if($command instanceof Command)
            {
                $this->add($command);
            }
        }
    }

    public function add(Command $command): self
    {
        $this->commands[$command->__toString()] = $command;

        return $this;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->commands);
    }

    public function count(): int
    {
        return count($this->commands);
    }
}
