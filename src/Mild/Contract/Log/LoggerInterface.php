<?php

namespace Mild\Contract\Log;

use Psr\Log\LoggerInterface as PsrLoggerInterface;

interface LoggerInterface extends PsrLoggerInterface
{
    /**
     * @return string
     */
    public function getChannel();

    /**
     * @return array
     */
    public function getHandlers();

    /**
     * @param HandlerInterface $handler
     * @return void
     */
    public function addHandler(HandlerInterface $handler);

    /**
     * @param $channel
     * @return void
     */
    public function setChannel($channel);

    /**
     * @param array $handlers
     * @return void
     */
    public function setHandlers(array $handlers);
}