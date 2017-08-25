<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Reporters;

use PhpParser\PrettyPrinter\Standard;
use Niktux\DDD\Analyzer\Reporter;
use Niktux\DDD\Analyzer\Domain\DefectCollection;
use Niktux\DDD\Analyzer\Domain\SortedDefectCollection;

class HTMLReporter implements Reporter
{
    private
        $content,
        $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->content = null;
        $this->twig = $twig;
    }

    public function render(SortedDefectCollection $defects): Reporter
    {
        $prettyPrint = new Standard();

        $this->content = $this->twig->render(
           'report.twig',
            array(
                'project' => 'DDD Analyzer',
                'defects' => $defects, //$this->sortDefectsByNamespace($defects),
                'printer' => $prettyPrint,
        ));

        return $this;
    }

    private function sortDefectsByNamespace(SortedDefectCollection $sortedCollection): SortedDefectCollection
    {
        $result = new SortedDefectCollection();

        foreach($sortedCollection as $bc => $collection)
        {
            $defects = iterator_to_array($collection);
            ksort($defects);
            $sorted = [];

            foreach($defects as $fileDefects)
            {
                $file = $fileDefects->getFile();
                $namespace = implode('/', explode('/', $file, -1));

                if(! isset($sorted[$namespace]))
                {
                    $sorted[$namespace] = array();
                }

                $sorted[$namespace][$file] = $fileDefects;
            }

            $newCollection = new DefectCollection();

            foreach($sorted as $array)
            {
                foreach($array as $defect)
                {
                    $newCollection->add($defect);
                }
            }

            $result->add($bc, $newCollection);
        }

        return $result;
    }

    public function save(string $reportFilename): void
    {
        file_put_contents($reportFilename, $this->content);
    }
}