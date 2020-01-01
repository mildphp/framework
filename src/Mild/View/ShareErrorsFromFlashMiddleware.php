<?php

namespace Mild\View;

use Mild\Session\Flash;
use Mild\Contract\Http\ResponseInterface;
use Mild\Contract\Http\ServerRequestInterface;

class ShareErrorsFromFlashMiddleware
{
    /**
     * @var Flash
     */
    protected $flash;
    /**
     * @var Factory
     */
    protected $factory;

    /**
     * ShareErrorsFromSessionMiddleware constructor.
     *
     * @param Flash $flash
     * @param Factory $factory
     */
    public function __construct(Flash $flash, Factory $factory)
    {
        $this->flash = $flash;
        $this->factory = $factory;
    }

    /**
     * @param ServerRequestInterface $request
     * @param $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, $next)
    {
        $this->factory->variable->get('__error')
            ->setItems($this->flash->get('__errors', []));

        return $next($request);
    }
}