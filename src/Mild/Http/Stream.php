<?php

namespace Mild\Http;

use RuntimeException;
use InvalidArgumentException;
use Mild\Contract\View\EngineInterface;
use Mild\Contract\Http\StreamInterface;

class Stream implements StreamInterface
{
    /**
     * @var int|null
     */
    protected $size;
    /**
     * @var resource
     */
    private $resource;
    /**
     * @var bool
     */
    protected $seekable;
    /**
     * @var bool
     */
    protected $readable;
    /**
     * @var bool
     */
    protected $writable;

    /**
     * Stream constructor.
     *
     * @param resource $resource
     */
    public function __construct($resource)
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException('The stream must be an resource type.');
        }
        $this->resource = $resource;
        $meta = stream_get_meta_data($resource);
        if (isset($meta['seekable'])) {
            $this->seekable = $meta['seekable'];
        }
        $this->readable = (bool) preg_match('/r|a\+|ab\+|w\+|wb\+|x\+|xb\+|c\+|cb\+/', $meta['mode']);
        $this->writable = (bool) preg_match('/a|w|r\+|rb\+|rw|x|c/', $meta['mode']);
    }

    /**
     * @return resource|null
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->size = null;
        $this->resource = null;
        $this->seekable = false;
        $this->readable = false;
        $this->writable = false;
        return $resource;
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
    {
        if ($this->size === null && $this->resource !== null) {
            $stats = fstat($this->resource);
            if (isset($stats['size'])) {
                $this->size = $stats['size'];
            }
        }
        return $this->size;
    }

    /**
     * @return bool|int
     */
    public function tell()
    {
        $this->assertDetachedStream();
        if (($tell = ftell($this->resource)) === false) {
            throw new RuntimeException('Uncaught position a stream.');
        }
        return $tell;
    }

    /**
     * @return bool
     */
    public function eof()
    {
        $this->assertDetachedStream();
        return feof($this->resource);
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        return $this->seekable;
    }

    /**
     * @param int $offset
     * @param int $whence
     * @return void
     */
    public function seek($offset, $whence = 0)
    {
        $this->assertDetachedStream();
        if (!$this->seekable || fseek($this->resource, $offset, $whence) === -1) {
            throw new RuntimeException('Could not seek in stream');
        }
    }

    /**
     * {inheritdoc}
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        return $this->writable;
    }

    /**
     * @param string $string
     * @return bool|int
     */
    public function write($string)
    {
        $this->assertDetachedStream();
        if ($string instanceof EngineInterface) {
            $string = $string->render();
        }
        if (!$this->writable || ($write = fwrite($this->resource, $string)) === false) {
            throw new RuntimeException('Could not write to stream');
        }
        $this->size = null;
        return $write;
    }

    /**
     * @param StreamInterface $stream
     * @return void
     */
    public function copy(StreamInterface $stream)
    {
        $this->assertDetachedStream();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        while (!$stream->eof()) {
            $this->write($stream->read(4096));
        }

        if ($this->isSeekable()) {
            $this->rewind();
        }
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        return $this->readable;
    }

    /**
     * @param int $length
     * @return string
     */
    public function read($length)
    {
        $this->assertDetachedStream();
        if ($length <= 0) {
            return '';
        }
        if (!$this->readable || ($data = fread($this->resource, $length)) === false) {
            throw new RuntimeException('Could not read from stream');
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getContents()
    {
        $this->assertDetachedStream();
        if (($content = stream_get_contents($this->resource)) === false) {
            throw new RuntimeException('Uncaught to read the stream.');
        }
        return $content;
    }

    /**
     * @param null $key
     * @return array|mixed|null
     */
    public function getMetadata($key = null)
    {
        if ($this->resource === null) {
            if ($key === null) {
                return $key;
            }
            return [];
        }
        $metaData = stream_get_meta_data($this->resource);
        if ($key === null) {
            return $metaData;
        }
        if (isset($metaData[$key])) {
            return $metaData[$key];
        }
        return null;
    }

    /**
     * {inheritdoc}
     */
    public function close()
    {
        if ($this->resource !== null) {
            fclose($this->resource);
            $this->detach();
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            $this->seek(0);
            return $this->getContents();
        } catch (RuntimeException $e) {
            return '';
        }
    }

    /**
     * {inheritdoc}
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return void
     */
    protected function assertDetachedStream()
    {
        if ($this->resource === null) {
            throw new RuntimeException('The stream has been detach.');
        }
    }
}