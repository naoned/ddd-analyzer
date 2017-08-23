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

        $this['analyzer'] = function($c) {
            $analyzer = new Analyzer($c['event.dispatcher'], $c['filesystem']);

            $analyzer->addVisitor(TraverseMode::analyze(), new ClassAliasingDetection(['DTO']));
            $analyzer->addVisitor(TraverseMode::analyze(), new AnonymousClassDetection());
            $analyzer->addVisitor(TraverseMode::analyze(), new BoundedContextDependency(new NamespaceInterpreter(2)));

            return $analyzer;
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
