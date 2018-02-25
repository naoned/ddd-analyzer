<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Services;

use Niktux\DDD\Analyzer\Domain\ValueObjects\FullyQualifiedName;
use Niktux\DDD\Analyzer\Domain\ValueObjects\InterpretedFQN;
use Niktux\DDD\Analyzer\Domain\ValueObjects\BoundedContext;
use Niktux\DDD\Analyzer\Domain\ValueObjects\Layer;

class NamespaceInterpreter
{
    private
        $nbPartsToSlice;

    public function __construct(int $nbPartsToSlice)
    {
        $this->nbPartsToSlice = $nbPartsToSlice;
    }

    public function canTranslate(FullyQualifiedName $fqn): bool
    {
        if(count($this->getParts($fqn)) >= $this->nbRequiredPartsAtLeast())
        {
            list($bc, $layer) = $this->getPartsFromBoundedContext($fqn);

            $notBc = ['Console', 'Controllers', 'Domain', 'Persistence', 'Projection', 'Services', 'Workers'];
            if(in_array($bc, $notBc))
            {
                return false;
            }

            return Layer::isValid($layer);
        }

        return false;
    }

    private function getParts(FullyQualifiedName $fqn): array
    {
        return explode('\\', $fqn->value());
    }

    private function getPartsFromBoundedContext(FullyQualifiedName $fqn): array
    {
        $parts = $this->getParts($fqn);

        return array_slice($parts, $this->nbPartsToSlice);
    }

    private function nbRequiredPartsAtLeast(): int
    {
        return $this->nbPartsToSlice + 3; // 3 = BC + Layer + Class
    }

    public function translate(FullyQualifiedName $fqn): InterpretedFQN
    {
        if(!$this->canTranslate($fqn))
        {
            throw new \LogicException("Namespace is not a DDD one : " . $fqn);
        }

        $parts = $this->getPartsFromBoundedContext($fqn);
        list($bc, $layer) = $parts;
        $relative = implode('\\', array_slice($parts, 2));

        return new InterpretedFQN(
            $fqn,
            new BoundedContext($bc),
            new Layer($layer),
            $relative
        );
    }
}
