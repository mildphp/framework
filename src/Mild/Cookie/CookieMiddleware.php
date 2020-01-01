<?php

namespace Mild\Cookie;

use Mild\Encryption\Encrypter;
use Mild\Encryption\EncryptionException;
use Mild\Contract\Http\ResponseInterface;
use Mild\Contract\Http\ServerRequestInterface;

class CookieMiddleware
{
    /**
     * @var Factory
     */
    protected $factory;
    /**
     * @var Encrypter
     */
    protected $encrypter;
    /**
     * @var array
     */
    protected $dontEncrypt = [];

    /**
     * CookieMiddleware constructor.
     *
     * @param Factory $factory
     * @param Encrypter $encrypter
     */
    public function __construct(Factory $factory, Encrypter $encrypter)
    {
        $this->factory = $factory;
        $this->encrypter = $encrypter;
    }

    /**
     * @param ServerRequestInterface $request
     * @param $next
     * @return ResponseInterface
     * @throws EncryptionException
     */
    public function __invoke(ServerRequestInterface $request, $next)
    {
        return $this->encrypt(
            $next($this->decrypt($request))
                ->withAddedHeader('Set-Cookie', $this->factory->getCookies())
        );
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws EncryptionException
     */
    protected function encrypt($response)
    {
        foreach ($response->getHeader('set-cookie') as $cookie) {
            if ($cookie instanceof Cookie && !in_array($cookie->getName(), $this->dontEncrypt)) {
                $cookie->setValue($this->encrypter->encrypt($cookie->getValue()));
            }
        }
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    protected function decrypt($request)
    {
        foreach (($cookies = $request->getCookieParams()) as $key => $value) {
            if (in_array($key, $this->dontEncrypt)) {
                continue;
            }
            try {
                $cookies[$key] = $this->encrypter->decrypt($value);
            } catch (EncryptionException $e) {
                //
            }
        }

        return $request->withCookieParams($cookies);
    }
}