<?php

namespace Mild\Http;

use Psr\Http\Message\RequestInterface;

class ClientRedirectMiddleware
{
    /**
     * @param RequestInterface $request
     * @param $next
     * @return mixed
     */
    public function __invoke(RequestInterface $request, $next)
    {
        /**
         * @var Response $response
         */
        if ($this->shouldRedirect($response = $next($request))) {
            return $this($request->withUri(Factory::createUri($response->getHeaderLine('Location'))), $next);
        }

        return $response;
    }

    /**
     * @param Response $response
     * @return bool
     */
    protected function shouldRedirect($response)
    {
        return substr($response->getStatusCode(), 0, 1) == 3 && $response->hasHeader('Location');
    }
}