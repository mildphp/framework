<?php

namespace Mild\Bootstrap;

use Mild\Contract\ProviderInterface;
use Mild\Contract\BootstrapInterface;
use Mild\Contract\ApplicationInterface;

class RegisterProviders implements BootstrapInterface
{

    /**
     * @param ApplicationInterface $application
     * @return void
     */
    public function bootstrap(ApplicationInterface $application)
    {
        foreach ($application->get('config')->get('app.providers') as $provider) {
            if ($provider instanceof ProviderInterface === false) {
                $provider = new $provider($application);
            }
            $application->provider($provider);
        }

        $application->boot();
    }
}