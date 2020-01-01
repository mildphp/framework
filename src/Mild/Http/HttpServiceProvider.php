<?php

namespace Mild\Http;

use RuntimeException;
use Mild\Support\ServiceProvider;
use Mild\Contract\Http\ClientInterface;

class HttpServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->application->alias(ClientInterface::class, Client::class);
    }

    /**
     * @return void
     */
    public function boot()
    {
        $this->addJsonMethodToResponse();

        $this->addViewMethodToResponse();

        $this->addCookieMethodToResponse();

        $this->addRedirectMethodToResponse();

        $this->addIpMethodToServerRequest();

        $this->addValidateMethodToServerRequest();
    }

    /**
     * @return void
     */
    protected function addJsonMethodToResponse()
    {
        Response::macro('json', function ($data, $options = 0) {
            /**
             * @var Response $response
             */
            $response = $this;
            $stream = $response->getBody();

            $stream->write(json_encode($data, $options));

            $stream->rewind();

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException(json_last_error_msg(), json_last_error());
            }

            return $response->withHeader('Content-Type', 'application/json;charset=utf-8');
        });
    }

    /**
     * @return void
     */
    protected function addViewMethodToResponse()
    {
        Response::macro('view', function ($view) {
            /**
             * @var Response $response
             */
            $response = $this;

            $stream = $response->getBody();

            $stream->write(view($view));

            return $this;
        });
    }

    /**
     * @return void
     */
    protected function addRedirectMethodToResponse()
    {
        Response::macro('redirect', function ($url, $status = 302) {
            /**
             * @var Response $response
             */
            $response = $this;

            return $response->withHeader('Location', $url)
                ->withStatus($status);
        });
    }

    /**
     * @return void
     */
    protected function addCookieMethodToResponse()
    {
        Response::macro('cookie', function ($cookie) {
            /**
             * @var Response $response
             */
            $response = $this;

            return $response->withAddedHeader('Set-Cookie', $cookie);
        });
    }

    /**
     * @return void
     */
    protected function addIpMethodToServerRequest()
    {
        ServerRequest::macro('ip', function () {
            $ip = 'UNKNOWN';
            /**
             * @var ServerRequest $request
             */
            $request = $this;

            $server = $request->getServerParams();
            if (isset($server['HTTP_CLIENT_IP'])) {
                $ip = $server['HTTP_CLIENT_IP'];
            } elseif(isset($server['HTTP_X_FORWARDED_FOR'])) {
                $ip = $server['HTTP_X_FORWARDED_FOR'];
            } elseif(isset($server['HTTP_X_FORWARDED'])) {
                $ip = $server['HTTP_X_FORWARDED'];
            } elseif(isset($server['HTTP_FORWARDED_FOR'])) {
                $ip = $server['HTTP_FORWARDED_FOR'];
            } elseif(isset($server['HTTP_FORWARDED'])) {
                $ip = $server['HTTP_FORWARDED'];
            } elseif(isset($server['REMOTE_ADDR'])) {
                $ip = $server['REMOTE_ADDR'];
            }

            return $ip;
        });
    }

    /**
     * @return void
     */
    protected function addValidateMethodToServerRequest()
    {
        ServerRequest::macro('validate', function ($rules, $messages = []) {
            /**
             * @var ServerRequest $request
             */
            $request = $this;
            validation(array_merge($request->getParsedBody(), $request->getQueryParams(), $request->getUploadedFiles()), $rules, $messages)->validate();
        });
    }
}