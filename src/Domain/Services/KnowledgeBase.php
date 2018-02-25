<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Services;

use Niktux\DDD\Analyzer\Domain\Collections\BoundedContextCollection;
use Niktux\DDD\Analyzer\Domain\Collections\CQS\QueryCollection;
use Niktux\DDD\Analyzer\Domain\Collections\CQS\CommandCollection;
use Niktux\DDD\Analyzer\Domain\Collections\TypeCollection;
use Niktux\DDD\Analyzer\Domain\Type;
use Niktux\DDD\Analyzer\Domain\ValueObjects\FullyQualifiedName;

class KnowledgeBase
{
    private
        $boundedContexts,
        $types,
        $queries,
        $commands;

    public function __construct()
    {
        $this->boundedContexts = new BoundedContextCollection();
        $this->types = new TypeCollection();
        $this->queries = new QueryCollection();
        $this->commands = new CommandCollection();
    }

    public function boundedContexts(): BoundedContextCollection
    {
        return $this->boundedContexts;
    }

    public function types(): TypeCollection
    {
        return $this->types;
    }

    public function queries(): QueryCollection
    {
        return $this->queries;
    }

    public function commands(): CommandCollection
    {
        return $this->commands;
    }

    public function loadType(string $fqnAsString): ?Type
    {
        return $this->types->get(
            new FullyQualifiedName($fqnAsString)
        );
    }
}
