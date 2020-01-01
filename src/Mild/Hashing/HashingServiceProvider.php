<?php

namespace Mild\Hashing;

use Mild\Support\ServiceProvider;
use Mild\Contract\ApplicationInterface;

class HashingServiceProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function register()
    {
        $this->application->set('hashing', function ($app) {
            /**
             * @var ApplicationInterface $app
             */

            $config = $app->get('config')->get('hashing');

            return new Factory(($driver = $config['driver'] ?? 'bcrypt'), $config['drivers'][$driver] ?? []);
        });

        $this->application->alias(Factory::class, 'hashing');
    }
}