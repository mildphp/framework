<?php

namespace Mild\Cookie;

use Mild\Support\ServiceProvider;
use Mild\Contract\Cookie\FactoryInterface;

class CookieServiceProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function register()
    {
        $this->application->set('cookie', Factory::class);
        $this->application->alias(Factory::class, 'cookie');
        $this->application->alias(FactoryInterface::class, 'cookie');
    }
}