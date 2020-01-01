<?php

namespace Mild\Database;

use Mild\Application;
use InvalidArgumentException;
use Mild\Database\Entity\Model;
use Mild\Support\ServiceProvider;
use Mild\Database\Query\Compilers\Compiler;
use Mild\Contract\Database\ConnectionInterface;
use Mild\Contract\Database\Query\CompilerInterface;

class DatabaseServiceProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function register()
    {
        $this->application->set('database', function ($app) {
            /**
             * @var Application $app
             */
            $config = $app->get('config')->get('database');

            if (!isset($config['drivers'][$config['driver']])) {
                throw new InvalidArgumentException(sprintf(
                    'Driver [%s] is not configured.', $config['driver']
                ));
            }

            $connection = (new Factory)->make($config['drivers'][$config['driver']]);

            $connection->setEventDispatcher($app->get('event'));

            return $connection;
        });

        $this->application->set('database.compiler', function ($app) {
            /**
             * @var Application $app
             */
            return $app->get('database')->getCompiler();
        });

        $this->application->alias(Connection::class, 'database');
        $this->application->alias(Compiler::class, 'database.compiler');
        $this->application->alias(ConnectionInterface::class, 'database');
        $this->application->alias(CompilerInterface::class, 'database.compiler');
    }

    /**
     * @return void
     */
    public function boot()
    {
        Model::setContainer($this->application);
    }
}