<?php

namespace Mild;

use Mild\Support\Arr;
use Mild\Container\Container;
use Mild\Log\LogServiceProvider;
use Mild\Event\EventServiceProvider;
use Mild\Contract\ProviderInterface;
use Mild\Contract\BootstrapInterface;
use Mild\Support\Events\LocaleUpdated;
use Mild\Contract\ApplicationInterface;
use Mild\Pipeline\PipelineServiceProvider;
use Mild\Contract\DeferrableProviderInterface;
use Mild\Support\Listeners\UpdateCarbonLocale;
use Mild\Support\Listeners\UpdateConfigLocale;
use Mild\Contract\Container\ContainerInterface;
use Mild\Contract\Event\EventDispatcherInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;

class Application extends Container implements ApplicationInterface
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $locale;
    /**
     * @var string
     */
    protected $basePath;
    /**
     * @var array
     */
    protected $providers = [];
    /**
     * @var bool
     */
    protected $booted = false;
    /**
     * @var array
     */
    protected $deferredProviders = [];
    /**
     * @var self
     */
    protected static $instance;
    /**
     * @var array
     */
    private $deferredProvides = [];

    /**
     * Application constructor.
     *
     * @param null $basePath
     */
    public function __construct($basePath = null)
    {
        self::$instance = $this;
        $this->setBasePath($basePath);
        $this->registerBaseBinding();
        $this->registerBaseProviders();
        $this->registerBaseEventsListeners();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * @param $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        $this[EventDispatcherInterface::class]->dispatch(new LocaleUpdated($locale));
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @param $basePath
     * @return void
     */
    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');
    }

    /**
     * @return array
     */
    public function getDeferredProviders()
    {
        return $this->deferredProviders;
    }

    /**
     * @return bool
     */
    public function runningInConsole()
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }

    /**
     * @param ProviderInterface $provider
     * @return void
     */
    public function provider(ProviderInterface $provider)
    {
        // Jika provider yang anda daftarkan sudah di daftarkan sebelumnya, maka kita akan
        // melewatinya.
        if (!isset($this->providers[$class = get_class($provider)])) {

            // Jika provider yang anda daftarkan termasuk provider yang di tangguhkan, maka kita
            // akan menagguhkan provider yang anda daftarkan.
            if ($provider instanceof DeferrableProviderInterface && !isset($this->deferredProviders[$class])) {

                // Kita akan mengambil kunci binding apa saja yang di defer di dalam provider yang
                // di tangguhkan, ini akan berguna ketika anda memanggil binding di dalam provider
                // yang di tangguhkan.
                foreach (($provides = Arr::wrap($provider->provides())) as $provide) {
                    $this->deferredProvides[$provide] = $provider;
                }

                $this->deferredProviders[$class] = compact('provider', 'provides');
            } else {
                $provider->register();
                if ($this->booted === true) {
                    $provider->boot();
                }
                $this->providers[$class] = $provider;
            }
        }
    }

    /**
     * @return void
     */
    public function boot()
    {
        if ($this->booted === false) {
            foreach ($this->providers as $provider) {
                $provider->boot();
            }
            $this->booted = true;
        }
    }

    /**
     * @return bool
     */
    public function isBooted()
    {
        return $this->booted;
    }

    /**
     * @param BootstrapInterface $bootstrap
     * @return void
     */
    public function bootstrap(BootstrapInterface $bootstrap)
    {
        $bootstrap->bootstrap($this);
    }

    /**
     * @return Application
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @return void
     */
    protected function registerBaseBinding()
    {
        $this->bind('app', $this);
        $this->alias('container', 'app');
        $this->alias(self::class, 'app');
        $this->alias(Container::class, 'app');
        $this->alias(ContainerInterface::class, 'app');
        $this->alias(ApplicationInterface::class, 'app');
        $this->alias(PsrContainerInterface::class, 'app');
    }

    /**
     * @return void
     */
    protected function registerBaseProviders()
    {
        $this->provider(new PipelineServiceProvider($this));
        $this->provider(new EventServiceProvider($this));
        $this->provider(new LogServiceProvider($this));
    }

    /**
     * @return void
     */
    protected function registerBaseEventsListeners()
    {
        $this[EventDispatcherInterface::class]->listen(LocaleUpdated::class, [
            UpdateConfigLocale::class,
            UpdateCarbonLocale::class
        ]);
    }

    /**
     * @param $key
     * @return string
     */
    protected function resolveKey($key)
    {
        if (isset($this->deferredProvides[$key])) {
            $this->provider($this->deferredProvides[$key]);
            foreach ($this->deferredProviders[$class = get_class($this->deferredProvides[$key])]['provides'] as $provide) {
                unset($this->deferredProvides[$provide]);
            }

            unset($this->deferredProviders[$class]);
        }

        return parent::resolveKey($key);
    }
}