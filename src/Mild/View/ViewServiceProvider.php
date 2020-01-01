<?php

namespace Mild\View;

use Mild\Application;
use Mild\Support\ServiceProvider;
use Mild\Contract\View\FactoryInterface;
use Mild\Contract\View\CompilerInterface;
use Mild\Contract\View\RepositoryInterface;
use Mild\Contract\Event\EventDispatcherInterface;

class ViewServiceProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function register()
    {
        $this->application->set('view', function ($app) {
            /**
             * @var Application $app
             */
            $config = $app->get('config')->get('view');
            return new Factory(
                $app->get(EventDispatcherInterface::class),
                new Repository($config['base_path']),
                new Compiler($config['compiled_path'])
            );
        });

        $this->application->set('view.compiler', function ($app) {
            /**
             * @var Application $app
             */
            return $app->get('view')->getCompiler();
        });

        $this->application->set('view.variable', function ($app) {
            /**
             * @var Application $app
             */
            return $app->get('view')->variable;
        });

        $this->application->set('view.repository', function ($app) {
            /**
             * @var Application $app
             */
            return $app->get('view')->getRepository();
        });

        $this->application->alias(Factory::class, 'view');
        $this->application->alias(FactoryInterface::class, 'view');
        $this->application->alias(Compiler::class, 'view.compiler');
        $this->application->alias(Repository::class, 'view.repository');
        $this->application->alias(CompilerInterface::class, 'view.compiler');
        $this->application->alias(RepositoryInterface::class, 'view.repository');
    }

    /**
     * @return void
     */
    public function boot()
    {
        $this->addWithMethodToEngine();
    }

    /**
     * @return void
     */
    protected function addWithMethodToEngine()
    {
        Engine::macro('with', function ($data) {
            /**
             * @var Engine $view
             */
            $view = $this;
            $view->addData($data);

            return $view;
        });
    }
}