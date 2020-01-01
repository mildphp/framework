<?php

namespace Mild\Contract;

interface ProviderInterface
{
    /**
     * @return void
     */
    public function boot();

    /**
     * @return void
     */
    public function register();

    /**
     * @return ApplicationInterface
     */
    public function getApplication();
}