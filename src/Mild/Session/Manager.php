<?php

namespace Mild\Session;

use RuntimeException;
use Mild\Support\Dot;
use Mild\Support\Str;
use SessionHandlerInterface;
use Mild\Contract\Session\ManagerInterface;

class Manager extends Dot implements ManagerInterface
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $prefix;
    /**
     * @var SessionHandlerInterface
     */
    protected $handler;
    /**
     * @var bool
     */
    protected $started = false;

    /**
     * Manager constructor.
     *
     * @param SessionHandlerInterface $handler
     * @param $name
     * @param string $prefix
     * @param string $id
     */
    public function __construct(SessionHandlerInterface $handler, $name, $prefix = '', $id = '')
    {
        $this->handler = $handler;
        $this->name = $name;
        $this->setPrefix($prefix);
        $this->setId($id);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return SessionHandlerInterface
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return bool
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * @param $id
     * @return void
     */
    public function setId($id)
    {
        $this->id = (is_string($id) && ctype_alnum($id) === true && strlen($id) === 40) ? $id : $this->generateSessionId();
    }

    /**
     * @param $prefix
     * @return void
     */
    public function setPrefix($prefix)
    {
        $this->prefix = trim($prefix, '\/');
    }

    /**
     * @return void
     */
    public function start()
    {
        if ($this->started === true) {
            throw new RuntimeException('Cannot start the session already started.');
        }
        if (($data = $this->handler->read($this->prefix.$this->id)) !== '' && ($data = @unserialize($data)) !== false) {
            $this->setItems($data);
        }
        $this->started = true;
    }

    /**
     * @param bool $destroy
     * @return void
     */
    public function regenerate($destroy = false)
    {
        if ($this->started === false) {
            throw new RuntimeException('Cannot regenerate the session id before start the session');
        }
        if ($destroy === true) {
            $this->handler->destroy($this->prefix.$this->id);
        }
        $this->setId($this->generateSessionId());
    }

    /**
     * @return void
     */
    public function save()
    {
        if ($this->started === false) {
            throw new RuntimeException('Cannot save the session before start the session');
        }
        $this->handler->write($this->prefix.$this->id, serialize($this->items));
        $this->started = false;
    }

    /**
     * @return string
     */
    protected function generateSessionId()
    {
        return Str::random(40);
    }
}