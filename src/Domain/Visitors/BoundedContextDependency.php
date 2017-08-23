<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Domain\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use Niktux\DDD\Analyzer\Domain\ContextualVisitor;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use Niktux\DDD\Analyzer\Domain\Defects\BoundedContextCouplage;
use Niktux\DDD\Analyzer\Domain\Defects\LayerViolationCall;

class BoundedContextDependency extends ContextualVisitor
{
    private
        $namespace = null;

    public function enter(Node $node)
    {
        if($node instanceof Namespace_)
        {
            $this->namespace = $this->analyze($node->name); // FIXME $node->name can be null
        }

        if($node instanceof Use_)
        {
            if($this->namespace !== null)
            {
                foreach($node->uses as $use)
                {
                    $dep = $this->analyze($use->name);

                    if($dep !== null)
                    {
                        $this->checkRule($node, $use->name, $dep);
                    }
                }
            }
        }
    }

    private function analyze(Name $node): ?array
    {
        $parts = $node->parts;

        if(count($parts) > 3)
        {
            if($parts[0] === 'Naoned' && $parts[1] === 'Kenao')
            {
                return $this->analyzeOwn($node, array_slice($parts, 2));
            }
        }

        return null;
    }

    private function analyzeOwn(Name $node, array $subparts): ?array
    {
        $bc = $subparts[0];

        $notBC = ['Console', 'Controllers', 'Domain', 'Persistence', 'Projection', 'Services', 'Workers'];

        if(in_array($bc, $notBC))
        {
            return null;
        }

        $layer = $subparts[1];
        $sub = isset($subparts[2]) ? $subparts[2] : null;

        return [$bc, $layer, $sub];
    }

    private function checkRule(Use_ $node, Name $dependency, array $dep)
    {
        list($nsBc, $nsLayer, $nsSub) = $this->namespace;
        list($depBc, $depLayer, $depSub) = $dep;

        if($nsBc !== $depBc)
        {
            $this->dispatch(new BoundedContextCouplage($node, $dependency, $nsBc, $depBc));
        }

        if($nsLayer !== $depLayer)
        {
            /*
            $allowedLayerCalls = [
                'Domain' => ['Domain'],
                'Application' => ['Domain', 'Application'],
                'Infrastructure' => ['Domain', 'Application', 'Infrastructure'],
            ]; //*/

            $layerDepth = [
                'Domain' => 0,
                'Application' => 1,
                'Infrastructure' => 2,
            ];

            $layers = array_keys($layerDepth);

            if(in_array($nsLayer, $layers) && in_array($depLayer, $layers))
            {
                $nsDepth = $layerDepth[$nsLayer];
                $depDepth = $layerDepth[$depLayer];

                if($depDepth > $nsDepth)
                {
                    $this->dispatch(new LayerViolationCall($node, $dependency, $nsBc, $nsLayer, $depLayer));
                }
            }
        }
    }
}
