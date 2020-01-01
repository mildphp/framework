<?php

namespace Mild\Http;

use Mild\Support\Str;
use Mild\Contract\Http\ResponseInterface;
use Mild\Contract\Session\ManagerInterface;
use Mild\Contract\Http\ServerRequestInterface;

class CsrfTokenMiddleware
{
    /**
     * @var array
     */
    protected $excepts = [];

    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * CsrfTokenMiddleware constructor.
     *
     * @param ManagerInterface $manager
     */
    public function __construct(ManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param $next
     * @return ResponseInterface
     * @throws TokenMisMatchException
     */
    public function __invoke(ServerRequestInterface $request, $next)
    {
        if (($token = $this->manager->get('_token')) === null) {
            $this->manager->set('_token', $this->generateToken());
        }

        if ($this->isReading($request->getMethod()) || $this->isExcept($request->getUri()->getPath()) || $this->isMatchToken($request, $token)) {
            return $next($request);
        }

        throw new TokenMisMatchException;
    }

    /**
     * @return string
     */
    protected function generateToken()
    {
        return Str::random();
    }

    /**
     * @param $method
     * @return bool
     */
    protected function isReading($method)
    {
        return $method === 'HEAD' || $method === 'GET' || $method === 'OPTIONS';
    }

    /**
     * @param string $path
     * @return bool
     */
    protected function isExcept($path)
    {
        foreach ($this->excepts as $except) {
            if (preg_match('#^'.preg_quote('/'.trim($except, '/'), '#').'\z#u', $path, $matches)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param ServerRequestInterface $request
     * @param $token
     * @return bool
     */
    protected function isMatchToken($request, $token)
    {
        if (($input = $request->getParsedBodyParam('_token')) === null) {
            $input = $request->getHeaderLine('X-CSRF-TOKEN');
        }
        return $input === $token;
    }
}