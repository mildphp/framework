<?php

namespace Mild\Session;

use Mild\Finder\Finder;
use SessionHandlerInterface;
use InvalidArgumentException;

class FileSessionHandler implements SessionHandlerInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * FileSessionHandler constructor.
     *
     * @param $path
     */
    public function __construct($path)
    {
        if (!is_writable($path)) {
            throw new InvalidArgumentException(sprintf(
                'Directory %s is not writable', $path
            ));
        }

        $this->path = rtrim($path, '\/');
    }

    /**
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * @param string $session_id
     * @return bool
     */
    public function destroy($session_id)
    {
        if (is_file($file = $this->path.DIRECTORY_SEPARATOR.$session_id)) {
            @unlink($file);
        }
        return true;
    }

    /**
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        foreach (Finder::instance($this->path)->files()->date(time() - $maxlifetime, '<=') as $file) {
            @unlink($file->getRealPath());
        }
        return true;
    }

    /**
     * @param string $save_path
     * @param string $name
     * @return bool
     */
    public function open($save_path, $name)
    {
        return true;
    }

    /**
     * @param string $session_id
     * @return string
     */
    public function read($session_id)
    {
        if (!is_file($file = $this->path.DIRECTORY_SEPARATOR.$session_id)) {
            return '';
        }
        return file_get_contents($file);
    }

    /**
     * @param string $session_id
     * @param string $session_data
     * @return bool
     */
    public function write($session_id, $session_data)
    {
        if (file_put_contents($this->path.DIRECTORY_SEPARATOR.$session_id, $session_data, LOCK_EX) === false) {
            return false;
        }
        return true;
    }
}