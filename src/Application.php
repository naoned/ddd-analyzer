<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer;

use Silex\Provider\SessionServiceProvider;
use Onyx\Providers;
use Gaufrette\Adapter\Local;
use Gaufrette\Filesystem;
use Niktux\DDD\Analyzer\Domain\Analyzer;
use Niktux\DDD\Analyzer\Domain\ValueObjects\TraverseMode;
use Niktux\DDD\Analyzer\Domain\Visitors\ClassAliasingDetection;
use Niktux\DDD\Analyzer\Domain\Visitors\AnonymousClassDetection;
use Niktux\DDD\Analyzer\Domain\Visitors\BoundedContextDependency;
use Niktux\DDD\Analyzer\Domain\NamespaceInterpreter;
use Niktux\DDD\Analyzer\Domain\DefectSorter;
use Puzzle\PrefixedConfiguration;

class Application extends \Onyx\Application
{
    protected function registerProviders(): void
    {
        $this->register(new SessionServiceProvider());
        $this->register(new Providers\Monolog([
            // insert your loggers here
        ]));
        $this->register(new Providers\Twig());
        $this->register(new Providers\Webpack());

        // Uncomment this line if you're using a RDBMS
        // $this->register(new Providers\DBAL());
    }

    protected function initializeServices(): void
    {
        $this->configureTwig();

        $this->initializeFilesystem();
        $this->initializeSubscribers();

        $this['event.dispatcher'] = function($c) {
            return new Dispatchers\EventDispatcher($c['dispatcher']);
        };

        $this['name.interpreter'] = function($c) {
            return new NamespaceInterpreter(2);
        };

        $this['defect.sorter'] = function($c) {
            return new DefectSorter($c['name.interpreter']);
        };

        $this['analyzer'] = function($c) {
            $config = $c['configuration'];
            $analyzer = new Analyzer($c['event.dispatcher'], $c['filesystem']);

            if($config->read('analyzer/skipTests', false))
            {
                $analyzer->skipTests();
            }

            $visitors = [
                'BoundedContextDependency',
                'AnonymousClassDetection',
                'ClassAliasingDetection',
            ];

            foreach($visitors as $visitor)
            {
                if($config->read("analyzer/$visitor/enabled", true))
                {
                    $analyzer->addVisitor(TraverseMode::analyze(), $c["visitors.$visitor"]);
                }
            }

            return $analyzer;
        };

        $this->initializeVisitors();
    }

    private function initializeVisitors()
    {
        $this['visitors.BoundedContextDependency'] = function($c) {
            return new BoundedContextDependency(
                $c['name.interpreter'],
                new PrefixedConfiguration($c['configuration'], "analyzer/BoundedContextDependency")
            );
        };

        $this['visitors.AnonymousClassDetection'] = function($c) {
            return new AnonymousClassDetection();
        };

        $this['visitors.ClassAliasingDetection'] = function($c) {
            return new ClassAliasingDetection(
                new PrefixedConfiguration($c['configuration'], "analyzer/ClassAliasingDetection")
            );
        };
    }

    private function initializeFilesystem()
    {
        $this['filesystem.path'] = 'src/';
        $this['filesystem.adapter'] = function($c) {
            return new Local($c['filesystem.path']);
        };

        $this['filesystem'] = function($c) {
            return new Filesystem($c['filesystem.adapter']);
        };
    }

    private function initializeSubscribers()
    {
        $this['subscriber.console'] = function($c) {
            return new EventSubscribers\Console();
        };

        $this['reporter.html'] = function($c) {
            return new Reporters\HTMLReporter($c['twig']);
        };

        $this['subscriber.html'] = function($c) {
            return new EventSubscribers\HTML($c['reporter.html'], $c['defect.sorter']);
        };
    }

    private function configureTwig(): void
    {
        $this['view.manager']->addPath(array(
            $this['root.path'] . 'views/',
        ));
    }

    protected function mountControllerProviders(): void
    {
        $this->mount('/', new Controllers\Home\Provider());
    }
}
