<?php

declare(strict_types = 1);

namespace Niktux\DDD\Analyzer;

use Puzzle\Configuration;
use Pimple\Container;
use Onyx\Services\Routes\Retrievers\Silex;
use Onyx\Console\Commands\RouteLister;

class Console
{
    private
        $app,
        $configuration;

    public function __construct(Container $container)
    {
        $this->configuration = $container['configuration'];

        $this->app = new \Onyx\Console\Application($container);

        $this->app->add(new RouteLister(new Silex($container)));
        $this->app->add(new Console\Run($container));
    }

    public function run(): void
    {
        $this->app->run();
    }
}
