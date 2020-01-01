<?php

namespace Mild\Bootstrap;

use ErrorException;
use Mild\Contract\BootstrapInterface;
use Mild\Contract\ApplicationInterface;
use Mild\Contract\ErrorHandlerInterface;
use Mild\Contract\Http\ServerRequestInterface;

class HandleException implements BootstrapInterface
{
    /**
     * @var ApplicationInterface
     */
    protected $application;

    /**
     * @param ApplicationInterface $application
     * @return void
     */
    public function bootstrap(ApplicationInterface $application)
    {
        $this->application = $application;

        error_reporting(-1);

        set_error_handler([$this, 'handleError']);

        set_exception_handler([$this, 'handleException']);

        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * @param $level
     * @param $message
     * @param string $file
     * @param int $line
     * @throws ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0)
    {
        if (error_reporting() & $level) {
            throw $this->createErrorException($message, $level, $file, $line);
        }
    }

    /**
     * @param $e
     * @return void
     */
    public function handleException($e)
    {
        /**
         * @var ErrorHandlerInterface $handler
         */
        $handler = $this->application->get(ErrorHandlerInterface::class);
        $handler->report($e);
        $handler->renderResponse($e, $this->application->get(ServerRequestInterface::class))->send();
    }

    /**
     * @return void
     */
    public function handleShutdown()
    {
        if (($error = error_get_last()) !== null && in_array($error['type'], [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE])) {
            $this->handleException($this->createErrorException(
                $error['message'],
                $error['type'],
                $error['file'],
                $error['line']
            ));
        }
    }

    /**
     * @param null $message
     * @param int $severity
     * @param null $file
     * @param int $line
     * @return ErrorException
     */
    protected function createErrorException($message = null, $severity = 0, $file = null, $line = 0)
    {
        return new ErrorException($message, 0, $severity, $file, $line);
    }
}