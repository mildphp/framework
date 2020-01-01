<?php

namespace Mild\Cache;

use Exception;
use Mild\Application;
use Mild\Support\ServiceProvider;
use Mild\Contract\Cache\ManagerInterface;
use Mild\Contract\Database\ConnectionInterface;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->application->set('cache', function ($app) {
            /**
             * @var Application $app
             */
            $config = $app->get('config')->get('cache');

            switch ($config['driver']) {
                case 'file':
                    $handler = new FileHandler($config['drivers'][$config['driver']]);
                    break;
                case 'apc':
                case 'apcu':
                    $handler =new ApcHandler;
                    break;
                case 'database':
                    $handler = new DatabaseHandler(
                        $app->get(ConnectionInterface::class),
                        $config['drivers'][$config['driver']]['table'],
                        $config['drivers'][$config['driver']]['columns'] ?? []
                    );
                    break;
                default:
                    throw new Exception(sprintf(
                        'Driver %s is not supported.', $config['driver']
                    ));
                    break;
            }

            return new Manager(
                $handler,
                $config['prefix'] ?? null
            );
        });

        $this->application->alias(Manager::class, 'cache');
        $this->application->alias(ManagerInterface::class, 'cache');
    }
}