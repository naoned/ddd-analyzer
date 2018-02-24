<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\EventSubscribers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Niktux\DDD\Analyzer\Events\TraverseEnd;
use Niktux\DDD\Analyzer\Events\ChangeFile;
use Niktux\DDD\Analyzer\Defect;
use Niktux\DDD\Analyzer\Domain\DefectCollection;
use Niktux\DDD\Analyzer\Domain\ContextualizedDefect;
use Niktux\DDD\Analyzer\Domain\DefectSorter;

class Json implements EventSubscriberInterface
{
    const
        DEFAULT_REPORT_FILENAME = 'report.json';

    private
        $sorter,
        $defects,
        $reportFilename,
        $currentFile;

    public function __construct(DefectSorter $sorter)
    {
        $this->sorter = $sorter;
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
        $event->formattedMessage = $this->formatMessage($event->getMessage());

        $defect = new ContextualizedDefect($event, $this->currentFile);

        $this->defects->add($defect);
    }

    private function formatMessage($message)
    {
        return strtr($message, array(
            'id>' => 'strong>',
            'type>' => 'strong>',
            'bc>' => 'strong>',
        ));
    }

    public function postMortemReport(TraverseEnd $event)
    {
        $data = [];

        foreach($this->defects as $defect)
        {
            $data[] = $defect->jsonSerialize();
        }

        file_put_contents($this->reportFilename, json_encode(['defects' => $data]));
    }
}