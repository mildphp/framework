<?php

namespace Mild\Mail;

use Exception;
use Mild\Application;
use Mild\Support\ServiceProvider;
use Mild\Contract\Mail\MailerInterface;
use Mild\Contract\Http\ServerRequestInterface;
use Mild\Contract\Event\EventDispatcherInterface;

class MailServiceProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function register()
    {
        $this->application->set('mail', function ($app) {

            /**
             * @var Application $app
             */
            $config = $app->get('config')->get('mail');

            switch ($config['driver']) {
                case 'smtp':
                    $driver = new SmtpTransport(
                        $config['drivers'][$config['driver']]['host'] ?? null,
                        $config['drivers'][$config['driver']]['port'] ?? null,
                        $config['drivers'][$config['driver']]['username'] ?? null,
                        $config['drivers'][$config['driver']]['password'] ?? null,
                        $config['drivers'][$config['driver']]['encryption'] ?? null,
                        $config['drivers'][$config['driver']]['timeout'] ?? 15,
                        $config['drivers'][$config['driver']]['auth'] ?? 'cram-md5'
                    );
                    break;
                case 'sendmail':
                    $driver = new SendMail;
                    break;
                default:
                    throw new Exception(sprintf(
                        'Unsupported [%s] driver', $config['driver']
                    ));
                    break;
            }

            $driver->setEventDispatcher($app->get(EventDispatcherInterface::class));

            return new Mailer(
                new IdGenerator($app->get(ServerRequestInterface::class)->getUri()->getHost()),
                $driver,
                $driver->getEventDispatcher()
            );
        });

        $this->application->alias(Mailer::class, 'mail');
        $this->application->alias(MailerInterface::class, 'mail');
    }
}