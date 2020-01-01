<?php

use Mild\Http\Uri;
use Mild\Application;
use Mild\View\Engine;
use Mild\Support\Arr;
use Mild\Http\Response;
use Mild\Session\Flash;
use Mild\Support\Optional;
use Mild\Config\Repository;
use Mild\Http\HttpException;
use Mild\Http\ServerRequest;
use Mild\Validation\Validator;
use Mild\Routing\UrlGenerator;
use Mild\Translation\Translator;
use Mild\Http\Factory as HttpFactory;
use Mild\View\Factory as ViewFactory;
use Mild\Contract\ApplicationInterface;
use Mild\Cookie\Factory as CookieFactory;
use Mild\Session\Manager as SessionManager;
use Mild\Validation\Factory as ValidationFactory;

if (!function_exists('app')) {
    /**
     * @param null $key
     * @return ApplicationInterface|mixed
     */
    function app($key = null)
    {
        /**
         * @var ApplicationInterface $app
         */
        $app = Application::getInstance();

        if ($key === null) {
            return $app;
        }

        return $app->make($key);
    }
}

if (!function_exists('env')) {
    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        return array_key_exists($key, $_ENV) ? $_ENV[$key] : $default;
    }
}

if (!function_exists('path')) {
    /**
     * @param null $path
     * @return string
     */
    function path($path = null) {
        $basePath = app()->getBasePath();
        if ($path === null || ($path = ltrim($path, '\/')) === '') {
            return $basePath;
        }
        return $basePath.DIRECTORY_SEPARATOR.$path;
    }
}

if (!function_exists('request')) {
    /**
     * @return ServerRequest
     */
    function request()
    {
        return app('request');
    }
}

if (!function_exists('response')) {
    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return Response
     */
    function response($code = 200, $reasonPhrase = '')
    {
        return HttpFactory::createResponse($code, $reasonPhrase);
    }
}

if (!function_exists('cookie')) {
    /**
     * @param $name
     * @param $value
     * @param int $expiration
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param null $sameSite
     * @return CookieFactory|void
     */
    function cookie($name = null, $value = '', $expiration = 0, $path = '/', $domain = '', $secure = false, $httpOnly = true, $sameSite = null)
    {
        /**
         * @var CookieFactory $cookie
         */
        $cookie = app('cookie');

        if ($name === null) {
            return $cookie;
        }

        $cookie->set($cookie->make($name, $value, $expiration, $path, $domain, $secure, $httpOnly, $sameSite));
    }
}

if (!function_exists('session')) {
    /**
     * @param null $key
     * @param null $value
     * @return SessionManager|mixed|void
     */
    function session($key = null, $value = null)
    {
        /**
         * @var SessionManager $session
         */
        $session = app('session');

        if ($key === null) {
            return $session;
        }

        if ($value === null) {
            return $session->get($key);
        }

        $session->set($key, $value);
    }
}

if (!function_exists('flash')) {
    /**
     * @param null $key
     * @param null $value
     * @return Flash|mixed|void
     */
    function flash($key = null, $value = null)
    {
        /**
         * @var Flash $flash
         */
        $flash = app('flash');

        if ($key === null) {
            return $flash;
        }

        if ($value === null) {
            return $flash->get($key);
        }

        $flash->set($key, $value);
    }
}

if (!function_exists('view')) {
    /**
     * @param $file
     * @param array $data
     * @return ViewFactory|Engine
     */
    function view($file = null, $data = [])
    {
        /**
         * @var ViewFactory @factory
         */
        $factory = app('view');

        if (null === $file) {
            return $factory;
        }

        return $factory->make($file, $data);
    }
}

if (!function_exists('tap')) {
    /**
     * @param $value
     * @param $callable
     * @return mixed
     */
    function tap($value, $callable)
    {
        $callable($value);
        return $value;
    }
}

if (!function_exists('url')) {
    /**
     * @param null $path
     * @return UrlGenerator|Uri
     */
    function url($path = null) {
        $url = app('url');

        if (null === $path) {
            return $url;
        }

        return $url->to($path);
    }
}

if (!function_exists('asset_url')) {
    /**
     * @param $path
     * @return UrlGenerator|Uri
     */
    function asset_url($path)
    {
        return url(trim(config('app.asset_path'), '/').'/'.trim($path, '/'));
    }
}

if (!function_exists('route')) {
    /**
     * @param $name
     * @param array $parameters
     * @return Uri
     */
    function route($name, $parameters = [])
    {
        return url()->route($name, Arr::wrap($parameters));
    }
}

if (!function_exists('abort')) {
    /**
     * @param $code
     * @param string $reasonPhrase
     * @throws HttpException
     */
    function abort($code, $reasonPhrase = '')
    {
        throw new HttpException($code, $reasonPhrase);
    }
}

if (!function_exists('config')) {
    /**
     * @param null $key
     * @param null $value
     * @return Repository|mixed|null|void
     */
    function config($key = null, $value = null)
    {
        /**
         * @var Repository $config
         */
        $config = app('config');

        if ($key === null) {
            return $config;
        }

        if ($value === null) {
            return $config->get($key);
        }

        $config->set($key, $value);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * @return string
     */
    function csrf_token()
    {
        return session('_token');
    }
}

if (!function_exists('csrf_field')) {
    /**
     * @return string
     */
    function csrf_field()
    {
        return '<input type=\'hidden\' name=\'_token\' value=\''.csrf_token().'\'/>';
    }
}

if (!function_exists('method')) {
    /**
     * @param $method
     * @return string
     */
    function method($method)
    {
        return '<input type=\'hidden\' name=\'_method\' value=\''.$method.'\'/>';
    }
}

if (!function_exists('optional')) {
    function optional ($value)
    {
        return new Optional($value);
    }
}

if (!function_exists('validation'))
{
    /**
     * @param $data
     * @param $rules
     * @param array $messages
     * @return Validator
     */
    function validation($data, $rules, $messages = [])
    {
        /**
         * @var ValidationFactory $validation
         */
        $validation = app('validation');

        return $validation->make($data, $rules, $messages);
    }
}

if (!function_exists('elapsed_time')) {
    /**
     * @param $start
     * @return string
     */
    function elapsed_time($start)
    {
        return number_format(microtime(true) - $start, 4);
    }
}

if (!function_exists('class_basename')) {
    /**
     * @param $class
     * @return string
     */
    function class_basename($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        return Arr::last(explode('\\', $class));
    }
}

if (!function_exists('trans')) {
    /**
     * @param $key
     * @param array $replacements
     * @param null $locale
     * @param bool $fallback
     * @return mixed
     */
    function trans($key, $replacements = [], $locale = null, $fallback = true)
    {
        /**
         * @var Translator $translator
         */
        $translator = app('translation');

        return $translator->get($key, $replacements, $locale, $fallback);
    }
}

if (!function_exists('__')) {
    /**
     * @param $key
     * @param array $replacements
     * @param null $locale
     * @param bool $fallback
     * @return mixed
     */
    function __($key, $replacements = [], $locale = null, $fallback = true)
    {
        return trans($key, $replacements, $locale, $fallback);
    }
}

if (!function_exists('old')) {
    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    function old($key, $default = null)
    {
        return flash()->get('__old.'.$key, $default);
    }
}