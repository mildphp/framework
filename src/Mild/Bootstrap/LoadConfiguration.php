<?php

namespace Mild\Bootstrap;

use Mild\Config\Repository;
use Mild\Config\Loader\EnvLoader;
use Mild\Contract\BootstrapInterface;
use Mild\Contract\ApplicationInterface;
use Mild\Config\Loader\LoaderException;
use Mild\Contract\Config\RepositoryInterface;
use Mild\Contract\Config\LoaderExceptionInterface;

class LoadConfiguration implements BootstrapInterface
{
    /**
     * @param ApplicationInterface $application
     * @throws LoaderException
     * @throws LoaderExceptionInterface
     */
    public function bootstrap(ApplicationInterface $application)
    {
        $repository = new Repository;

        $_ENV += (new EnvLoader)->load(path('.env'));

        if (is_file($cache = $application->get('config.cache.path'))) {
            $repository->setItems(require ''.$cache.'');
        } else {
            $repository->load($application->get('config.path'));
        }

        mb_internal_encoding('UTF-8');

        $application->bind('config', $repository);
        $application->alias(Repository::class, 'config');
        $application->alias(RepositoryInterface::class, 'config');

        $application->setName($repository->get('app.name'));

        $application->setLocale($repository->get('app.locale'));

        date_default_timezone_set($repository->get('app.timezone'));
    }
}