<?php

namespace Mild\Support\Listeners;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Carbon\CarbonInterval;
use Carbon\CarbonImmutable;
use Mild\Support\Events\LocaleUpdated;

class UpdateCarbonLocale
{
    /**
     * @param LocaleUpdated $event
     * @return void
     */
    public function __invoke(LocaleUpdated $event)
    {
        Carbon::setLocale($event->locale);

        CarbonPeriod::setLocale($event->locale);

        CarbonInterval::setLocale($event->locale);

        CarbonImmutable::setLocale($event->locale);
    }
}