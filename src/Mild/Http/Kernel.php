<?php

namespace Mild\Http;

use Closure;
use Throwable;
use Mild\Routing\Route;
use Mild\Routing\RouteEvent;
use Mild\Routing\UrlGenerator;
use Mild\Bootstrap\RegisterFacades;
use Mild\Bootstrap\HandleException;
use Mild\Bootstrap\RegisterProviders;
use Mild\Bootstrap\LoadConfiguration;
use Mild\Contract\Http\KernelInterface;
use Mild\Contract\ApplicationInterface;
use Mild\Contract\ErrorHandlerInterface;
use Mild\Contract\Http\ResponseInterface;
use Mild\Contract\Routing\RouteInterface;
use Mild\Contract\Pipeline\PipelineInterface;
use Mild\Contract\Http\ServerRequestInterface;
use Mild\Contract\Event\EventDispatcherInterface;
use Mild\Contract\Routing\RouteCollectionInterface;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ServerRequestInterface as PsrServerRequestInterface;

class Kernel implements KernelInterface
{
    /**
     * @var ApplicationInterface
     */
    private $application;
    /**
     * @var array
     */
    private $bootstrappers = [
        HandleException::class,
        LoadConfiguration::class,
        RegisterFacades::class,
        RegisterProviders::class
    ];

    /**
     * Kernel constructor.
     *
     * @param ApplicationInterface $application
     */
    public function __construct(ApplicationInterface $application)
    {
        $this->application = $application;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request)
    {
        try {
            // Pertama kita akan me-registrasi request ke dalam container, karena mungkin beberapa
            // service provide membutuhkan request untuk menjalankan service.
            $this->registerRequestContainerBinding($request);

            $this->bootstrapApplication();

            $this->registerRouteBindingToContainer($route = $this->findRoute($request));

            // Setelah route di temukan, kita akan memasukan route ke dalam request, anda bisa
            // memanggil route melewati container ataupun melewati request.
            $this->registerRequestContainerBinding($request = $request->withRoute($route));

            $response = $this->application->make(PipelineInterface::class, [$route->middleware()])
                ->send($this->dispatchRoute(), $request);
        } catch (Throwable $e) {
            $response = $this->handleError($e, $request);
        }

        $this->application->get(EventDispatcherInterface::class)->dispatch(
            new RequestHandledEvent($request, $response)
        );

        return $response;
    }

    /**
     * @param $e
     * @param $request
     * @return ResponseInterface
     */
    protected function handleError($e, $request)
    {
        /**
         * @var ErrorHandlerInterface $handler
         */
        $handler = $this->application->get(ErrorHandlerInterface::class);

        $handler->report($e);

        return $handler->renderResponse($e, $request);
    }

    /**
     * @param ServerRequestInterface $request
     * @return Route
     * @throws MethodNotAllowedException
     * @throws NotFoundHttpException
     */
    protected function findRoute($request)
    {
        $found = false;
        $routeNames = [];
        $currentRoute = null;
        $uri = $request->getUri();
        $host = $uri->getHost();
        $path = $uri->getPath();
        $parts = explode('/',$request->getServerParam('SCRIPT_NAME'));
        $file = array_pop($parts);
        $path = substr($path, strlen($base = implode('/', $parts)));
        $parts = explode('/', $path);

        // Jika di dalam url terdapat root file, maka kita akan hilangkan
        // root file tersebut.
        if (isset($parts[1]) && $parts[1] === $file) {
            unset($parts[1]);
        }

        // Kita akan hapus semua trailing slash yang ada di dalam path
        $path = '/'.trim(implode('/', $parts), '/');

        /**
         * @var Route $route
         */
        foreach ($this->application->get(RouteCollectionInterface::class)->getRoutes() as $route) {
            if (($name = $route->name())) {
                $routeNames[$name] = $route;
            }
            if (!$route->match($host, $path)) {
                continue;
            }
            $found = true;
            if (in_array(strtoupper($request->getParsedBodyParam('_method', $request->getMethod())), array_map('strtoupper', $route->getMethods()))) {
                $currentRoute = $route;
            }
        }

        // Mendaftarkan url ke dalam container
        $this->application->bind('url', new UrlGenerator($uri->withPath($base), $routeNames));

        // Jika route ditemukan maka kita akan mengembalikan route yang di temukan.
        if (null !== $currentRoute) {
            return $currentRoute;
        }

        // Jika route tidak di temukan, maka kita akan lemparkan error NotFoundHttpException,
        // tetapi jika route di temukan tetapi request method tidak valid dengan route yang
        // anda, maka kita akan lemparkan error MethodNotAllowedException.
        // Error ini akan mengirimkan response dengan status code yang sudah di tentukan,
        // semisalnya untuk NotFoundHttpException akan mengirimkan response [404], begitu
        // pula dengan MethodNotAllowedException akan mengirimkan response [405].
        if ($found === false) {
            throw new NotFoundHttpException;
        }

        throw new MethodNotAllowedException;
    }

    /**
     * @return Closure
     */
    protected function dispatchRoute()
    {
        return function (ServerRequestInterface $request) {

            $this->registerRequestContainerBinding($request);

            try {
                /**
                 * @var Route $route
                 */
                $route = $request->getRoute();

                $output = $this->application->make($route->controller(), array_values($route->getParameters()));

                $this->application->get(EventDispatcherInterface::class)->dispatch(new RouteEvent($route));

                if ($output instanceof ResponseInterface) {
                    return $output;
                }

                $response = Factory::createResponse();

                $response->getBody()->write($output);

                return $response;
            } catch (Throwable $e) {
                return $this->handleError($e, $request);
            }
        };
    }

    /**
     * @param ServerRequestInterface $request
     * @return void
     */
    protected function registerRequestContainerBinding($request)
    {
        $this->application->bind('request', $request);
        $this->application->alias(Request::class, 'request');
        $this->application->alias(ServerRequest::class, 'request');
        $this->application->alias(PsrRequestInterface::class, 'request');
        $this->application->alias(ServerRequestInterface::class, 'request');
        $this->application->alias(PsrServerRequestInterface::class, 'request');
    }

    /**
     * @param RouteInterface $route
     * @return void
     */
    protected function registerRouteBindingToContainer($route)
    {
        $this->application->bind('route', $route);
        $this->application->alias(Route::class, 'route');
        $this->application->alias(RouteInterface::class, 'route');
    }

    /**
     * @return void
     */
    protected function bootstrapApplication()
    {
        foreach ($this->bootstrappers as $bootstrapper) {
            $this->application->bootstrap($this->application->make($bootstrapper));
        }
    }
}