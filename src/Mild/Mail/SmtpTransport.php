<?php

namespace Mild\Mail;

use Exception;
use Mild\Support\Arr;
use Mild\Contract\Mail\MessageInterface;

class SmtpTransport extends AbstractTransport
{
    /**
     * @var resource
     */
    private $resource;
    /**
     * @var int
     */
    private $port;
    /**
     * @var string
     */
    private $host;
    /**
     * @var string
     */
    private $username;
    /**
     * @var string
     */
    private $password;
    /**
     * @var null
     */
    private $encryption;
    /**
     * @var int
     */
    private $timeout;
    /**
     * @var string
     */
    private $auth;

    /**
     * SmtpTransport constructor.
     *
     * @param $host
     * @param $port
     * @param $username
     * @param $password
     * @param null $encryption
     * @param int $timeout
     * @param string $auth
     * @throws MailException
     */
    public function __construct($host, $port, $username, $password, $encryption = null, $timeout = 15, $auth = 'login')
    {
        if (strpos($host, '://')) {
            throw new MailException('Host cannot contain scheme.');
        }

        if (($encryption = strtolower($encryption)) === 'ssl') {
            $host = 'ssl://'.$host;
        }

        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->encryption = $encryption;
        $this->timeout = $timeout;
        $this->auth = strtolower($auth);
    }

    /**
     * @param MessageInterface|Message $message
     * @throws MailException|Exception
     */
    public function send(MessageInterface $message)
    {
        $time = microtime(true);

        $data = $message->toString();

        if (!($this->resource = @stream_socket_client($this->host.':'.$this->port, $errno, $errstr, $this->timeout, STREAM_CLIENT_CONNECT))) {
            throw new MailException($errstr);
        }

        stream_set_blocking($this->resource, 1);
        stream_set_timeout($this->resource, $this->timeout);

        $this->dispatchEvent(new SmtpConnectedEvent(elapsed_time($time), $this->response(220), $this->host, $this->port, $this->timeout));

        try {
            $this->command('EHLO '.$message->generator->host, 250);
        } catch (MailException $e) {
            $this->command('HELO '.$message->generator->host, 250);
        }

        if ($this->encryption === 'tls') {
            $this->command('STARTTLS', 220);
            stream_socket_enable_crypto($this->resource, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        }

        switch ($this->auth) {
            case 'plain':
                $this->command('AUTH PLAIN', 334);
                $this->command(base64_encode($this->username.chr(0).$this->username.chr(0).$this->password), 235);
                break;
            case 'login':
                $this->command('AUTH LOGIN', 334);
                $this->command(base64_encode($this->username), 334);
                $this->command(base64_encode($this->password), 235);
                break;
            case 'cram-md5':
                $this->command(base64_encode(
                    $this->username.' '.hash_hmac('md5', base64_decode(substr($this->command('AUTH CRAM-MD5', 334), 4)), $this->password, false
                    )), 235);
                break;
            default:
                throw new MailException(sprintf(
                    'Unsupported [%s] auth type.', $this->auth
                ));
                break;
        }

       $this->command(sprintf(
           'MAIL FROM: <%s>', $message->getFrom()
       ), 250);

       foreach ($message->getRecipients() as $recipient) {
           $this->command(sprintf(
               'RCPT TO: <%s>', $recipient
           ), [250, 251]);
       }

       $this->command('DATA', 354);
       $this->command($data);
       $this->command('.', 250);

        $this->dispatchEvent(
            new SmtpDisconnectedEvent($this->command('QUIT', 221))
        );

        fclose($this->resource);

        $this->resource = null;
    }

    /**
     * @param $command
     * @param array $code
     * @return string
     * @throws MailException
     */
    private function command($command, $code = [])
    {
        fwrite($this->resource, $command."\r\n");

        $this->dispatchEvent(new SmtpCommandExecutedEvent($command, $response = $this->response($code)));

        return $response;
    }

    /**
     * @param null $code
     * @return string
     * @throws MailException
     */
    private function response($code = null)
    {
        $response = '';

        fflush($this->resource);

        if (!empty($code = Arr::wrap($code))) {
            while (($message = fgets($this->resource))) {
                $response .= $message;

                if ($message[3] == ' ') {
                    break;
                }
            }

            if (!$this->isExpectedCode(substr($response, 0, 3), $code)) {
                throw new MailException($response);
            }
        }

        return $response;
    }

    /**
     * @param $code
     * @param $expectations
     * @return bool
     */
    private function isExpectedCode($code, $expectations)
    {
        foreach ($expectations as $expectation) {
            if ($code == $expectation) {
                return true;
            }
        }

        return false;
    }
}