<?php

namespace Mild\Http;

use Mild\Contract\Http\ResponseInterface;
use Mild\Contract\Http\ServerRequestInterface;

class ValidatePostSizeMiddleware
{

    /**
     * @param ServerRequestInterface $request
     * @param $next
     * @return ResponseInterface
     * @throws PostTooLargeException
     */
    public function __invoke(ServerRequestInterface $request, $next)
    {
        if (($size = $this->getPostMaxSize()) > 0 && $request->getServerParam('CONTENT_LENGTH') > $size) {
            throw new PostTooLargeException;
        }

        return $next($request);
    }

    /**
     * @return int
     */
    protected function getPostMaxSize()
    {
        switch (strtoupper(($size = ini_get('post_max_size'))[-1])) {
            case 'K':
                return (int) $size * 1024;
                break;
            case 'M':
                return (int) $size * 1048576;
                break;
            case 'G':
                return (int) $size * 1073741824;
                break;
            default:
                return (int) $size;
                break;
        }
    }
}