<?php

namespace Mild\Mail;

use Mild\Support\Arr;
use Mild\Contract\Mail\BodyInterface;

class Body implements BodyInterface
{
    /**
     * @var HeaderCollection
     */
    protected $header;
    /**
     * @var string
     */
    private $contents;

    /**
     * Body constructor.
     *
     * @param $contents
     */
    public function __construct($contents)
    {
        $this->contents = $contents;
        $this->header = new HeaderCollection;
    }

    /**
     * @return HeaderCollection
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return string
     * @throws MailException
     */
    public function toString()
    {
        $encoding = Arr::first($this->header->get('Content-Transfer-Encoding'));

        if (($result = $this->header->toString())) {
            $result .= "\r\n";
        }

        return $result."\r\n".($encoding ? Encoder::encode($this->contents, $encoding) : $this->contents);
    }
}