<?php

namespace Mild\Http;

use RuntimeException;
use InvalidArgumentException;
use Mild\Contract\Http\StreamInterface;
use Mild\Contract\Http\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{
    /**
     * @var int
     */
    protected $size;
    /**
     * @var int
     */
    protected $error;
    /**
     * @var StreamInterface
     */
    protected $stream;
    /**
     * @var string
     */
    protected $extension;
    /**
     * @var bool
     */
    protected $moved = false;
    /**
     * @var null
     */
    protected $clientFileName;
    /**
     * @var null
     */
    protected $clientMediaType;
    /**
     * @var array
     */
    private static $errors = [
        UPLOAD_ERR_OK => 'There is no error, the file uploaded with success.',
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the size directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the size directive that was specified in the HTML form.',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
    ];

    /**
     * UploadedFile constructor.
     *
     * @param StreamInterface $stream
     * @param int $size
     * @param int $error
     * @param null $clientFileName
     * @param null $clientMediaType
     */
    public function __construct(
        StreamInterface $stream,
        $size = 0,
        $error = UPLOAD_ERR_OK,
        $clientFileName = null,
        $clientMediaType = null
    )
    {
        $this->stream = $stream;
        $this->size = $size;
        if (!isset(self::$errors[$error])) {
            throw new InvalidArgumentException('The error uploaded file is invalid.');
        }
        $this->error = $error;
        $this->clientFileName = $clientFileName;
        $this->clientMediaType = $clientMediaType;
        $this->extension = strtolower(pathinfo($clientFileName, PATHINFO_EXTENSION));
    }

    /**
     * @return StreamInterface
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * @param string $targetPath
     * @return void
     */
    public function moveTo($targetPath)
    {
        $this->validateErrorMoved();
        if (!is_string($targetPath) || $targetPath === '') {
            throw new InvalidArgumentException('The target path is not provided.');
        }
        if (false === ($handle = fopen($targetPath, 'wb+'))) {
            throw new RuntimeException('Cannot open the target path.');
        }
        $stream = new Stream($handle);
        $stream->copy($this->stream);
        $stream->close();
        $this->stream->close();
        $this->moved = true;
    }

    /**
     * @return bool
     */
    public function isMoved()
    {
        return $this->moved;
    }

    /**
     * @return int|null
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @return string|null
     */
    public function getClientFilename()
    {
        return $this->clientFileName;
    }

    /**
     * @return string|null
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }

    /**
     * {inheritdoc}
     */
    protected function validateErrorMoved()
    {
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException(self::$errors[$this->error]);
        }
        if ($this->moved === true) {
            throw new RuntimeException('The file has been uploaded.');
        }
    }
}