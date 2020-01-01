<?php

namespace Mild\Bootstrap;

use Mild\Support\Facades\Facade;
use Mild\Contract\BootstrapInterface;
use Mild\Contract\ApplicationInterface;

class RegisterFacades implements BootstrapInterface
{
    /**
     * @var array
     */
    private $aliases = [];

    /**
     * @param ApplicationInterface $application
     */
    public function bootstrap(ApplicationInterface $application)
    {
        Facade::setApplication($application);

        $this->aliases = $application->get('config')->get('app.aliases', []);

        spl_autoload_register([$this, 'loadAlias'], true, true);
    }

    /**
     * @param $alias
     * @return void
     */
    private function loadAlias($alias)
    {
        if (isset($this->aliases[$alias])) {
            class_alias($this->aliases[$alias], $alias);
        }
    }
}