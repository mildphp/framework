<?php

namespace Mild\Translation;

use Mild\Application;
use Mild\Contract\Database\ConnectionInterface;
use Mild\Support\ServiceProvider;
use Mild\Support\Events\LocaleUpdated;
use Mild\Contract\DeferrableProviderInterface;
use Mild\Contract\Event\EventDispatcherInterface;
use Mild\Contract\Translation\RepositoryInterface;
use Mild\Contract\Translation\TranslatorInterface;


class TranslationServiceProvider extends ServiceProvider implements DeferrableProviderInterface
{
    /**
     * @return void
     */
    public function register()
    {
        $app = $this->application;

        Factory::macro('database', function ($table, $columns) use ($app) {
            return new DatabaseRepository($app->get(ConnectionInterface::class),$table, $columns);
        });

        $this->application->set('translation', function ($app) {
            /**
             * @var Application $app
             */
            return Factory::make($app->get('config')->get('translation', []), $app->getLocale());
        });

        $this->application->set('translation.repository', function ($app) {
            /**
             * @var Application $app
             */
            return $app->get(TranslatorInterface::class)->getRepository();
        });

        $this->application->alias(Translator::class, 'translation');
        $this->application->alias(TranslatorInterface::class, 'translation');
        $this->application->alias(Repository::class, 'translation.repository');
        $this->application->alias(RepositoryInterface::class, 'translation.repository');
    }

    /**
     * @return void
     */
    public function boot()
    {
        $this->application->get(EventDispatcherInterface::class)
            ->listen(LocaleUpdated::class, UpdateTranslationLocale::class);
    }

    /**
     * @return string|array
     */
    public function provides()
    {
        return ['translation', 'translation.repository', Translator::class, TranslatorInterface::class, Repository::class, RepositoryInterface::class];
    }
}