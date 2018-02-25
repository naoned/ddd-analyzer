<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain;

use Niktux\DDD\Analyzer\Domain\ValueObjects\InterpretedFQN;
use Niktux\DDD\Analyzer\Events\Defect;
use Niktux\DDD\Analyzer\Domain\ValueObjects\BoundedContext;
use Niktux\DDD\Analyzer\Domain\ValueObjects\FullyQualifiedName;
use Niktux\DDD\Analyzer\Domain\Collections\DefectRelatedCollection;
use Niktux\DDD\Analyzer\Domain\Collections\DefectCollection;
use Niktux\DDD\Analyzer\Domain\Collections\SortedDefectCollection;
use Niktux\DDD\Analyzer\Domain\Services\NamespaceInterpreter;

class DefectSorter
{
    private
        $bcs,
        $interpreter;

    public function __construct(NamespaceInterpreter $interpreter)
    {
        $this->bcs = new SortedDefectCollection();
        $this->interpreter = $interpreter;
    }

    public function sort(DefectCollection $defects): SortedDefectCollection
    {
        foreach($defects as $entry)
        {
            $context = $this->contextualize($entry->getDefect());

            $bc = BoundedContext::unknown();
            if($context instanceof InterpretedFQN)
            {
                $bc = $context->boundedContext();
            }

            $this->ensureBCCollectionExists($bc);

            $this->bcs->addIn($bc->value(), $entry);
        }

        $this->sortBoundedContextCollections();

        return $this->bcs;
    }

    private function contextualize(Defect $defect): ?InterpretedFQN
    {
        $fqn = $defect->getNamespace();

        if(! $fqn instanceof FullyQualifiedName)
        {
            return null;
        }

        if($this->interpreter->canTranslate($fqn))
        {
            return $this->interpreter->translate($fqn);
        }

        return null;
    }

    private function ensureBCCollectionExists(BoundedContext $bc)
    {
        $this->bcs->addIfNotExists($bc->value(), new DefectCollection());
    }

    private function sortBoundedContextCollections()
    {
        $sorted = new SortedDefectCollection();

        foreach($this->bcs as $bc => $collection)
        {
            $sorted->add($bc, $this->sortCollectionByDefectType($collection));
        }

        $this->bcs = $sorted;
    }

    private function sortCollectionByDefectType(DefectCollection $collection): DefectRelatedCollection
    {
        $sorted = new SortedDefectCollection();

        foreach($collection as $defect)
        {
            $id = get_class($defect->getDefect());
            $sorted->addIfNotExists($id, new DefectCollection());

            $defects = $sorted->get($id);

            $defects->add($defect);
        }

        return $sorted;
    }
}
