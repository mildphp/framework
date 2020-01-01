<?php

namespace Mild\Contract\Http;

use Mild\Contract\Pipeline\PipelineInterface;
use Psr\Http\Client\ClientInterface as PsrClientInterface;

interface ClientInterface extends PsrClientInterface
{
    /**
     * @return ClientHandlerInterface
     */
    public function getHandler();

    /**
     * @return PipelineInterface
     */
    public function getPipeline();
}