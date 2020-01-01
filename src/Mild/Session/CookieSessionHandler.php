<?php

namespace Mild\Session;

use SessionHandlerInterface;
use Mild\Contract\Cookie\FactoryInterface;
use Mild\Contract\Http\ServerRequestInterface;

class CookieSessionHandler implements SessionHandlerInterface
{
    /**
     * @var FactoryInterface
     */
    private $cookie;
    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * CookieSessionHandler constructor.
     *
     * @param FactoryInterface $cookie
     * @param ServerRequestInterface $request
     */
    public function __construct(FactoryInterface $cookie, ServerRequestInterface $request)
    {
        $this->cookie = $cookie;
        $this->request = $request;
    }

    /**
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * @param string $session_id
     * @return bool
     */
    public function destroy($session_id)
    {
        $this->cookie->set(
            $this->cookie->forget($session_id)
        );
        return true;
    }

    /**
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return true;
    }

    /**
     * @param string $save_path
     * @param string $name
     * @return bool
     */
    public function open($save_path, $name)
    {
        return true;
    }

    /**
     * @param string $session_id
     * @return mixed|string|null
     */
    public function read($session_id)
    {
        return $this->request->getCookieParam($session_id, '');
    }

    /**
     * @param string $session_id
     * @param string $session_data
     * @return bool
     */
    public function write($session_id, $session_data)
    {
        $this->cookie->set(
            $this->cookie->make($session_id, $session_data)
        );
        return true;
    }
}