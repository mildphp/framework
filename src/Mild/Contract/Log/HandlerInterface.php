<?php

namespace Mild\Contract\Log;

interface HandlerInterface
{
    /**
     * @return string
     */
    public function getMinLevel();

    /**
     * @param $level
     * @return void
     */
    public function setMinLevel($level);

    /**
     * @param $channel
     * @param $level
     * @param $message
     * @param array $context
     * @return void
     */
    public function handle($channel, $level, $message, array $context = []);
}