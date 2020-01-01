<?php

namespace Mild\Session;

use Exception;
use Mild\Cookie\Cookie;
use Mild\Contract\ApplicationInterface;
use Mild\Contract\Http\ResponseInterface;
use Mild\Contract\Http\ServerRequestInterface;

class StartSessionMiddleware
{
    /**
     * @var ApplicationInterface
     */
    private $application;

    /**
     * StartSessionMiddleware constructor.
     *
     * @param ApplicationInterface $application
     */
    public function __construct(ApplicationInterface $application)
    {
        $this->application = $application;
    }

    /**
     * @param ServerRequestInterface $request
     * @param $next
     * @return ResponseInterface
     * @throws Exception
     */
    public function __invoke(ServerRequestInterface $request, $next)
    {
        /**
         * @var Manager $manager
         */
        $manager = $this->application->get('session');
        $config = $this->application->get('config')->get('session');
        $name = $manager->getName();
        $manager->setId($request->getCookieParam($name));
        $manager->start();
        if (random_int(1, $config['lottery'][1]) <= $config['lottery'][0]) {
            $manager->getHandler()->gc($config['lifetime']);
        }
        $response = $next($request)
            ->withAddedHeader('Set-Cookie', new Cookie(
                $name,
                $manager->getId()
            ));

        $manager->save();

        return $response;
    }
}