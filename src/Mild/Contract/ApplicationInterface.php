<?php

namespace Mild\Contract;

use Mild\Contract\Container\ContainerInterface;

interface ApplicationInterface extends ContainerInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getLocale();

    /**
     * @return string
     */
    public function getBasePath();

    /**
     * @return array
     */
    public function getProviders();

    /**
     * @param $name
     * @return void
     */
    public function setName($name);

    /**
     * @return bool
     */
    public function runningInConsole();

    /**
     * @return array
     */
    public function getDeferredProviders();

    /**
     * @param $locale
     * @return void
     */
    public function setLocale($locale);

    /**
     * @param $basePath
     * @return void
     */
    public function setBasePath($basePath);

    /**
     * @return void
     */
    public function boot();

    /**
     * @return bool
     */
    public function isBooted();

    /**
     * @param ProviderInterface $provider
     * @return void
     */
    public function provider(ProviderInterface $provider);

    /**
     * @param BootstrapInterface $bootstrap
     * @return void
     */
    public function bootstrap(BootstrapInterface $bootstrap);
}