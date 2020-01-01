<?php

namespace Mild\Log;

use Mild\Application;
use Mild\Http\Client;
use Psr\Log\LogLevel;
use Mild\Support\Arr;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Mild\Support\ServiceProvider;
use Mild\Contract\Log\HandlerInterface;
use Mild\Contract\Mail\MailerInterface;
use Mild\Contract\Database\ConnectionInterface;

class LogServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->application->set('log', function ($app) {
            /**
             * @var Application $app
             */
            $config = $app->get('config')->get('logging');

            $logger = new Logger;

            if (isset($config['channel'])) {
                $logger->setChannel($config['channel']);
            }

            if (isset($config['driver'])) {
                $level = LogLevel::DEBUG;

                foreach (Arr::wrap($config['driver']) as $driver) {
                    if ($driver instanceof HandlerInterface) {
                        continue;
                    }

                    switch ($driver) {
                        case 'mail':
                            $logger->addHandler(new MailHandler($app->get(MailerInterface::class), $config['drivers'][$driver]['resolver'], $config['drivers'][$driver]['level'] ?? $level));
                            break;
                        case 'stream':
                            $logger->addHandler(new StreamHandler($config['drivers'][$driver]['path'], $config['drivers'][$driver]['level'] ?? $level));
                            break;
                        case 'browser':
                            $logger->addHandler(new BrowserHandler($config['drivers'][$driver]['level'] ?? $level));
                            break;
                        case 'database':
                            $logger->addHandler(new DatabaseHandler($app->get(ConnectionInterface::class), $config['drivers'][$driver]['table'] ?? 'logs', $config['drivers'][$driver]['column'] ?? [], $config['drivers'][$driver]['level'] ?? $level));
                            break;
                        case 'slack_webhook':
                            $logger->addHandler(new SlackWebhookHandler($app->make(Client::class), $config['drivers'][$driver]['url'], $config['drivers'][$driver]['channel'] ?? null, $config['drivers'][$driver]['username'] ?? null, $config['drivers'][$driver]['icon'] ?? null, $config['drivers'][$driver]['level'] ?? $level));
                            break;
                        default:
                            throw new InvalidArgumentException(sprintf(
                                'Driver [%s] is not configured.', $driver
                            ));
                            break;
                    }
                }
            }

            return $logger;
        });

        $this->application->alias(Logger::class, 'log');
        $this->application->alias(LoggerInterface::class, 'log');
    }
}