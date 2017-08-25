<?php

namespace Niktux\DDD\Analyzer\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Niktux\DDD\Analyzer\Domain\Analyzer;
use Pimple\Container;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputOption;

class Run extends Command
{
    private
        $container;

    public function __construct(Container $container)
    {
        parent::__construct();

        $this->container = $container;
    }

    protected function configure()
    {
        $this->setName('analyze')
            ->setDescription('Run anlysis')
            ->addArgument('src', InputArgument::REQUIRED, 'sources to analyze')
            ->addOption('htmlReport', null, InputOption::VALUE_REQUIRED, 'HTML report filename');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configureOutputs($input, $output);

        $this->container['filesystem.path'] = $input->getArgument('src');

        $analyzer = $this->container['analyzer'];
        $analyzer->run();
    }

    private function configureOutputs(InputInterface $input, OutputInterface $output)
    {
        $dispatcher = $this->container['dispatcher'];

        $this->enableConsoleOutput($dispatcher, $output);
        $this->enableHtmlReport($dispatcher, $input->getOption('htmlReport'));
    }

    private function enableConsoleOutput(EventDispatcherInterface $dispatcher, OutputInterface $output)
    {
        $console = $this->container['subscriber.console'];
        $console->setOutput($output);
        $dispatcher->addSubscriber($console);
    }

    private function enableHtmlReport(EventDispatcherInterface $dispatcher, $htmlReportFilename)
    {
        if($htmlReportFilename !== null)
        {
            $html = $this->container['subscriber.html'];
            $html->setReportFilename($htmlReportFilename);

            $dispatcher->addSubscriber($html);
        }
    }
}
