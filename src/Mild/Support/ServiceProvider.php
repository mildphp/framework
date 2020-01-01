<?php

namespace Mild\Support;

use Mild\Contract\ProviderInterface;
use Mild\Contract\ApplicationInterface;
use Mild\View\Repository as ViewRepository;
use Mild\Config\Repository as ConfigRepository;

abstract class ServiceProvider implements ProviderInterface
{
    /**
     * @var ApplicationInterface
     */
    protected $application;

    /**
     * ServiceProvider constructor.
     *
     * @param ApplicationInterface $application
     */
    public function __construct(ApplicationInterface $application)
    {
        $this->application = $application;
    }

    /**
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * @return ApplicationInterface
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param $path
     * @return void
     */
    protected function loadRoutesFrom($path)
    {
        if (!is_file($file = $this->application->get('route.cache.path'))) {
            require ''.$path.'';
        }
    }

    /**
     * @param $path
     * @param $space
     * @return void
     */
    protected function loadViewsFrom($path, $space)
    {
        $this->application->get(ViewRepository::class)
            ->setSpace($space, new ViewRepository($path));
    }

    /**
     * @param $path
     * @param $name
     * @return void
     */
    protected function mergeConfigFrom($path, $name)
    {
        $this->application->get(ConfigRepository::class)->load($path, $name);
    }
}