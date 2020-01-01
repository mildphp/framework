<?php

namespace Mild\Contract;

interface BootstrapInterface
{
    /**
     * @param ApplicationInterface $application
     * @return void
     */
    public function bootstrap(ApplicationInterface $application);
}