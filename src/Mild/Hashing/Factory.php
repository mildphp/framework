<?php

namespace Mild\Hashing;

use Exception;
use Mild\Support\Traits\Macroable;
use Mild\Contract\Hashing\HasherInterface;

class Factory
{
    use Macroable;

    /**
     * @var HasherInterface
     */
    protected $driver;
    /**
     * @var array
     */
    protected $config;
    /**
     * @var array
     */
    private $drivers = [
        'bcrypt'    => BcryptHasher::class,
        'argon2I'   => Argon2IHasher::class,
        'argon2ID'  => Argon2IDHasher::class
    ];

    /**
     * Factory constructor.
     *
     * @param $driver
     * @param array $config
     * @throws Exception
     */
    public function __construct($driver, $config = [])
    {
        if (isset($this->drivers[$driver])) {
            $driver = $this->drivers[$driver];
        } elseif (self::hasMacro($driver)) {
            $driver = self::$driver();
        } else {
            throw new Exception(sprintf(
                'Unsupported [%s] driver.', $driver
            ));
        }

        if (false === is_subclass_of($driver, HasherInterface::class)) {
            throw new Exception('The driver must be implemented %s', HasherInterface::class);
        }

        $this->driver = $driver;

        $this->setConfig($config);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function hash($value)
    {
        return $this->driver::hash($value, $this->config);
    }

    /**
     * @param $hash
     * @return bool
     */
    public function rehash($hash)
    {
        return $this->driver::rehash($hash, $this->config);
    }

    /**
     * @param $value
     * @param $hash
     * @return bool
     */
    public function check($value, $hash)
    {
        return $this->driver::check($value, $hash);
    }

    /**
     * @param $hash
     * @return array
     */
    public function info($hash)
    {
        return $this->driver::info($hash);
    }

    /**
     * @param array $config
     * @return void
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return HasherInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }
}