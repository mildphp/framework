<?php

namespace Mild\Http;

use InvalidArgumentException;
use Mild\Support\Traits\Macroable;
use Mild\Contract\Http\StreamInterface;
use Mild\Contract\Http\ResponseInterface;

class Response implements ResponseInterface
{
    use Macroable, MessageTrait;

    /**
     * @var int
     */
    protected $statusCode;
    /**
     * @var string
     */
    protected $reasonPhrase;
    /**
     * @var array
     */
    private static $phrases = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required'
    ];

    /**
     * Response constructor.
     *
     * @param StreamInterface $body
     * @param int $statusCode
     * @param string $reasonPhrase
     * @param array $headers
     * @param string $protocolVersion
     */
    public function __construct(
        StreamInterface $body,
        $statusCode = 200,
        $reasonPhrase = '',
        array $headers = [],
        $protocolVersion = '1.0'
    )
    {
        $this->assertStatusCode($statusCode);
        $this->statusCode = $statusCode;
        if ($reasonPhrase === '' && isset(self::$phrases[$statusCode])) {
            $reasonPhrase = self::$phrases[$statusCode];
        }
        $this->reasonPhrase = $reasonPhrase;
        $this->body = $body;
        $this->setHeaders($headers);
        $this->assertProtocolVersion($protocolVersion);
        $this->protocolVersion = $protocolVersion;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param $statusCode
     * @param string $reasonPhrase
     * @return static
     */
    public function withStatus($statusCode, $reasonPhrase = '')
    {
        $clone = clone $this;
        $this->assertStatusCode($statusCode);
        $clone->statusCode = $statusCode;
        if (trim($reasonPhrase, ' ') === '' && isset(self::$phrases[$statusCode])) {
            $reasonPhrase = self::$phrases[$statusCode];
        }
        $clone->reasonPhrase = $reasonPhrase;
        return $clone;
    }

    /**
     * @return string
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    /**
     * @return void
     */
    public function send()
    {
        if (!headers_sent()) {
            foreach ($this->headers as $name => $values) {
                foreach ($values as $value) {
                    header($name.': '.$value, false);
                }
            }

            header('HTTP/'.$this->protocolVersion.' '.$this->statusCode.' '.$this->reasonPhrase, true, $this->statusCode);
        }

        (new Stream(fopen('php://output', 'wb')))->copy($this->body);
    }

    /**
     * @param $statusCode
     * @return void
     */
    protected function assertStatusCode($statusCode)
    {
        if (filter_var($statusCode, FILTER_VALIDATE_INT) === false || $statusCode < 100 || $statusCode >= 600) {
            throw new InvalidArgumentException('The status code must be integer value between 100 and 600.');
        }
    }
}