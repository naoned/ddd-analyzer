<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer\EventSubscribers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Niktux\DDD\Analyzer\Events\Defect;
use Niktux\DDD\Analyzer\Events\TraverseEnd;
use Niktux\DDD\Analyzer\Events\ChangeFile;

class Console implements EventSubscriberInterface
{
    private
        $messages,
        $counter,
        $currentFile,
        $output;

    public static function getSubscribedEvents()
    {
        return array(
            Defect::EVENT_NAME => array('onDefect'),
            TraverseEnd::EVENT_NAME => array('postMortemReport'),
            ChangeFile::EVENT_NAME => array('setCurrentFile'),
        );
    }

    public function __construct()
    {
        $this->messages = [];
        $this->output = new NullOutput();
        $this->currentFile = null;
        $this->counter = 0;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    public function setCurrentFile(ChangeFile $event)
    {
        $this->currentFile = $event->getCurrentFile();

        return $this;
    }

    public function onDefect(Defect $event)
    {
        $this->messages[] = sprintf(
            "<fg=white;options=bold>%s @ l%d</fg=white;options=bold> : %s",
            $this->currentFile,
            $event->getLine(),
            $this->formatMessage($event->getMessage())
        );

        $this->counter++;
    }

    private function formatMessage($message)
    {
        return strtr($message, array(
            'id>' => 'comment>',
            'type>' => 'info>',
            'bc>' => 'question>',
        ));
    }

    public function postMortemReport(TraverseEnd $event)
    {
        $this->output->writeln($this->messages);

        $this->output->writeln(sprintf(
            '<comment>%d defect%s found</comment>',
            $this->counter,
            $this->counter > 1 ? 's' : ''
        ));
    }
}