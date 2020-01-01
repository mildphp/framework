<?php

namespace Mild\Session;

use Mild\Support\Dot;
use RuntimeException;
use Mild\Contract\Session\FlashInterface;
use Mild\Contract\Session\ManagerInterface;

class Flash extends Dot implements FlashInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ManagerInterface
     */
    protected $manager;

    /**
     * Sebelum anda menggunakan flash, anda melakukan start session terlebih dahulu
     * agar flash data yang masuk bisa di handle oleh session.
     *
     * @param ManagerInterface $manager
     * @param string $name
     */
    public function __construct(ManagerInterface $manager, $name = '_flash')
    {
        if ($manager->isStarted() === false) {
            throw new RuntimeException('The session is not started');
        }
        $this->setItems($manager->get($name, []));
        $manager->put($name);
        $this->manager = $manager;
        $this->setName($name);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * @return ManagerInterface
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function set($key, $value)
    {
        $this->manager->set($this->resolveKey($key), $value);
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function add($key, $value)
    {
        $this->manager->add($this->resolveKey($key), $value);
    }

    private function resolveKey($key)
    {
        return $this->name.'.'.$key;
    }
}