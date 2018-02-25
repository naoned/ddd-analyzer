<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\Reporters;

use PhpParser\PrettyPrinter\Standard;
use Niktux\DDD\Analyzer\Reporter;
use Niktux\DDD\Analyzer\Domain\Collections\SortedDefectCollection;

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
                'defects' => $defects,
                'printer' => $prettyPrint,
        ));

        return $this;
    }

    public function save(string $reportFilename): void
    {
        file_put_contents($reportFilename, $this->content);
    }
}