<?php

namespace Mild\Log;

use Throwable;
use Mild\Http\Stream;
use Psr\Log\LogLevel;

class BrowserHandler extends AbstractHandler
{

    /**
     * @param $channel
     * @param $level
     * @param $message
     * @param $context
     * @return void
     */
    protected function writeLog($channel, $level, $message, $context)
    {
        if (php_sapi_name() !== 'cli' || php_sapi_name() !== 'phpdbg') {
            $script = $this->generateJavascript($channel, $level, $message, $context);

            $stream = new Stream(fopen('php://output', 'wb'));

            $stream->write(
                <<<HTML
<script >
    (function () {
        {$script}
    })()
</script>
HTML

            );
        }
    }

    /**
     * @param $channel
     * @param $level
     * @param $message
     * @param array $context
     * @return string
     */
    private function generateJavascript($channel, $level, $message, $context)
    {
        switch ($level) {
            case LogLevel::ERROR:
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
            case LogLevel::EMERGENCY:
                $bg = 'red';
                break;
            case LogLevel::WARNING:
                $bg = 'yellow';
                break;
            default:
                $bg = 'blue';
                break;
        }

        $level = strtoupper($level);

        if ($context) {
            return <<<JS
            console.groupCollapsed("%c%c{$channel}%c %c{$level}%c {$message}", "font-weight: normal", "font-weight: bold", "font-weight: normal", "font-weight: bold; background-color: {$bg}; color: white; border-radius: 3px; padding: 0 2px 0 2px", "font-weight: normal");
            console.log("%cContext", "font-weight: bold");
            console.log({$this->composeContext($context)});
            console.groupEnd();
JS;
        }

        return <<<JS
        console.log("%c%c{$channel}%c %c{$level}%c {$message}", "font-weight: normal", "font-weight: bold", "font-weight: normal", "font-weight: bold; background-color: {$bg}; color: white; border-radius: 3px; padding: 0 2px 0 2px", "font-weight: normal");
JS;


    }

    /**
     * @param array $context
     * @return string
     */
    private function composeContext($context)
    {
        if (!$context) {
            return '';
        }

        foreach ($context as $key => $value) {
            if ($value instanceof Throwable) {
                $context[$key] = [
                    'code' => $value->getCode(),
                    'file' => $value->getFile(),
                    'line' => $value->getLine(),
                    'message' => $value->getMessage()
                ];
            }
        }

        return json_encode($context);
    }
}