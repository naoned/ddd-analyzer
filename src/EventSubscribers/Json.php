<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\EventSubscribers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Niktux\DDD\Analyzer\Events\TraverseEnd;
use Niktux\DDD\Analyzer\Events\ChangeFile;
use Niktux\DDD\Analyzer\Events\Defect;
use Niktux\DDD\Analyzer\Domain\Collections\DefectCollection;
use Niktux\DDD\Analyzer\Domain\ContextualizedDefect;
use Niktux\DDD\Analyzer\Domain\Services\KnowledgeBase;
use Niktux\DDD\Analyzer\Domain\ValueObjects\FullyQualifiedName;

class Json implements EventSubscriberInterface
{
    const
        DEFAULT_REPORT_FILENAME = 'report.json';

    private
        $base,
        $defects,
        $reportFilename,
        $currentFile;

    public function __construct(KnowledgeBase $base)
    {
        $this->base = $base;

        $this->defects = new DefectCollection();
        $this->reportFilename = self::DEFAULT_REPORT_FILENAME;
        $this->currentFile = null;
    }

    public function setReportFilename($filename)
    {
        $this->reportFilename = $filename;

        return $this;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Defect::EVENT_NAME => array('onDefect'),
            TraverseEnd::EVENT_NAME => array('postMortemReport'),
            ChangeFile::EVENT_NAME => array('setCurrentFile'),
        );
    }

    public function setCurrentFile(ChangeFile $event)
    {
        $this->currentFile = $event->getCurrentFile();

        return $this;
    }

    public function onDefect(Defect $event)
    {
        $this->defects->add(
            new ContextualizedDefect($event, $this->currentFile)
        );
    }

    public function postMortemReport(TraverseEnd $event)
    {
        $data = [
            'bounded_contexts' => [],
            'types' => [],
            'queries' => [],
            'commands' => [],
            'defects' => [],
        ];

        foreach($this->base->boundedContexts() as $bc)
        {
            $data['bounded_contexts'][] = (string) $bc;
        }

        $serviceProvider = $this->base->types()->get(new FullyQualifiedName("Pimple\\ServiceProviderInterface"));
        foreach($this->base->types() as $type)
        {
            if($type->isA($serviceProvider) === false)
            {
                $data['types'][] = $type->jsonSerialize();
            }
        }

        foreach($this->base->queries() as $query)
        {
            $data['queries'][] = $query->jsonSerialize();
        }

        foreach($this->base->commands() as $command)
        {
            $data['commands'][] = $command->jsonSerialize();
        }

        foreach($this->defects as $defect)
        {
            $data['defects'][] = $defect->jsonSerialize();
        }

        $summary = [
            'report_time' => date(DATE_ISO8601),
        ];

        foreach($data as $key => $values)
        {
            $summary[$key] = count($values);
        }

        $data = ['summary' => $summary] + $data;

        file_put_contents($this->reportFilename, json_encode($data));
    }
}