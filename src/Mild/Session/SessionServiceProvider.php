<?php

namespace Mild\Session;

use Exception;
use Mild\Application;
use Mild\Support\ServiceProvider;
use Mild\Contract\Session\FlashInterface;
use Mild\Contract\Cookie\FactoryInterface;
use Mild\Contract\Session\ManagerInterface;
use Mild\Contract\Http\ServerRequestInterface;
use Mild\Contract\Database\ConnectionInterface;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->application->set('session', function ($app) {
            /**
             * @var Application $app
             */
            $config = $app->get('config')->get('session');
            switch ($driver = $config['driver']) {
                case 'file':
                    $handler = new FileSessionHandler($config['drivers'][$driver]);
                    break;
                case 'cookie':
                    $handler = new CookieSessionHandler($app->get(FactoryInterface::class), $app->get(ServerRequestInterface::class));
                    break;
                case 'database':
                    $handler = new DatabaseSessionHandler($app->get(ConnectionInterface::class), $config['drivers'][$driver]['table'] ?? 'sessions', $config['drivers'][$driver]['columns'] ?? [], $config['lifetime'] ?? 7200);
                    break;
                default:
                    throw new Exception(sprintf(
                        'Unsupported [%s] driver.', $driver
                    ));
                    break;
            }
            return new Manager($handler, $config['name'], $config['prefix']);
        });

        $this->application->set('flash', function ($app) {
            /**
             * @var Application $app
             */
            return new Flash($app->get('session'), $app->get('config')->get('session.flash'));
        });

        $this->application->alias(Flash::class, 'flash');
        $this->application->alias(Manager::class, 'session');
        $this->application->alias(FlashInterface::class, 'flash');
        $this->application->alias(ManagerInterface::class, 'session');
    }
}