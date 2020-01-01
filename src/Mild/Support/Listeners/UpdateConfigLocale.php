<?php

namespace Mild\Support\Listeners;

use Mild\Support\Events\LocaleUpdated;
use Mild\Contract\Config\RepositoryInterface;

class UpdateConfigLocale
{
    /**
     * @param LocaleUpdated $event
     * @param RepositoryInterface $repository
     */
    public function __invoke(LocaleUpdated $event, RepositoryInterface $repository)
    {
        $repository->set('app.locale', $event->locale);
    }
}