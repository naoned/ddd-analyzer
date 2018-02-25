<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\ValueObjects\CQS;

use Niktux\DDD\Analyzer\Domain\ValueObjects\InterpretedFQN;

final class Query extends AbstractCQSItem
{
    public static function isValid(InterpretedFQN $fqn): bool
    {
        return self::isQueryOrCommand($fqn, 'Queries', 'Query');
    }
}
