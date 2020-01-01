<?php

namespace Mild;

use Throwable;
use Whoops\Run;
use Mild\Http\Factory;
use Psr\Log\LoggerInterface;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Handler\JsonResponseHandler;
use Mild\Contract\ApplicationInterface;
use Mild\Contract\ErrorHandlerInterface;
use Mild\Contract\Http\ResponseInterface;
use Mild\Contract\Http\HttpExceptionInterface;
use Mild\Contract\Http\ServerRequestInterface;
use Mild\Contract\Validation\ValidationExceptionInterface;
use Whoops\Handler\HandlerInterface as WhoopsHandlerInterface;
use Mild\Contract\View\FactoryInterface as ViewFactoryInterface;

class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @var ApplicationInterface
     */
    protected $application;

    /**
     * Handler constructor.
     *
     * @param ApplicationInterface $application
     */
    public function __construct(ApplicationInterface $application)
    {
        $this->application = $application;
    }

    /**
     * @return ApplicationInterface
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param Throwable $e
     * @return void
     */
    public function report(Throwable $e)
    {
        try {
            $this->application->get(LoggerInterface::class)->error($e->getMessage(), [
                get_class($e) => $e
            ]);
        } catch (Throwable $e) {
            //
        }
    }

    /**
     * @param Throwable $e
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function renderResponse(Throwable $e, ServerRequestInterface $request)
    {
        $response = Factory::createResponse(500);

        if ($request->isXml()) {
            $handler = new XmlResponseHandler;
        } elseif ($request->isPlain()) {
            $handler = new PlainTextHandler;
        } elseif ($request->isXhr() || $request->isJson()) {
            $handler = new JsonResponseHandler;
        } else {
            $handler = new PrettyPageHandler;
        }

        $content = $this->createWhoopsInstance($handler)->handleException($e);

        try {
            if ($e instanceof ValidationExceptionInterface) {

                $validator = $e->getValidator();

                $flash = $this->application->get('flash');

                $flash->add('__old', $validator->getData()->all());

                $flash->add('__errors.validation', $validator->getMessage());
                return $response->withStatus(301)
                    ->withHeader('Location', [$request->getServerParam('HTTP_REFERER', $this->application->get('url'))]);
            }

            $isHttpException = false;

            if ($e instanceof HttpExceptionInterface) {
                $isHttpException = true;
                $response = $response->withStatus($e->getStatusCode(), $e->getReasonPhrase());
            }

            if ($isHttpException || !$this->application->get('config')->get('app.debug')) {
                $view = $this->application->get(ViewFactoryInterface::class);
                $content = $view->getRepository()->has($file = $this->viewForRenderException($response->getStatusCode())) ? $view->make($file) : '';
            }
        } catch (Throwable $e) {
            //
        }

        $response->getBody()->write($content);

        return $response;
    }

    /**
     * @param WhoopsHandlerInterface $handler
     * @return Run
     */
    protected function createWhoopsInstance(WhoopsHandlerInterface $handler)
    {
        $whoops = new Run;

        $whoops->appendHandler($handler);

        $whoops->allowQuit(false);

        $whoops->writeToOutput(false);

        return $whoops;
    }

    /**
     * @param $code
     * @return string
     */
    protected function viewForRenderException($code)
    {
        return 'errors.'.$code;
    }
}