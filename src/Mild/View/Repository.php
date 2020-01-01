<?php

namespace Mild\View;

use Mild\Support\Dot;
use Mild\Finder\Finder;
use InvalidArgumentException;
use Mild\Contract\View\RepositoryInterface;

class Repository extends Dot implements RepositoryInterface
{
    /**
     * @var string
     */
    const EXTENSION = 'mld';
    /**
     * @var string
     */
    protected $path;
    /**
     * @var array
     */
    protected $spaces = [];

    /**
     * Repository constructor.
     *
     * @param $path
     * @param array $spaces
     */
    public function __construct($path, $spaces = [])
    {
        foreach (($finder = Finder::instance($path)->files()->extensions(self::EXTENSION)) as $file) {
            if ($prefix = trim(str_replace($path, '', ($directory = $file->getPath())), DIRECTORY_SEPARATOR)) {
                $prefix = $this->replaceSeparator($prefix).'.';
            }
            $this->set($prefix.pathinfo($file, PATHINFO_FILENAME), $file->getRealPath());
        }

        $this->path = $finder->getPath();

        $this->setSpaces($spaces);
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        if ($this->containSpace($key)) {

            [$key, $value] = $this->parseSpace($key);

            if ($this->hasSpace($key)) {
                return $this->getSpace($key)->has($value);
            }

            return false;
        }

        return parent::has($this->replaceSeparator($key));
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->containSpace($key)) {
            return $this->getFromSpace($key);
        }

        return parent::get($this->replaceSeparator($key), $this->path.DIRECTORY_SEPARATOR.$key);
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function set($key, $value)
    {
        parent::set($this->replaceSeparator($key), $value);
    }

    /**
     * @param $key
     * @return void
     */
    public function put($key)
    {
        if ($this->containSpace($key)) {
            [$key, $value] = $this->parseSpace($key);
            $this->getSpace($key)->put($value);
        } else {
            parent::put($this->replaceSeparator($key));
        }
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getSpaces()
    {
        return $this->spaces;
    }

    /**
     * @param $key
     * @return RepositoryInterface
     */
    public function getSpace($key)
    {
        if (!$this->hasSpace($key)) {
            throw new InvalidArgumentException(sprintf(
                'Space [%s] is not defined.', $key
            ));
        }

        return $this->spaces[$key];
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasSpace($key)
    {
        return isset($this->spaces[$key]);
    }

    /**
     * @param $key
     * @param RepositoryInterface $value
     * @return void
     */
    public function setSpace($key, RepositoryInterface $value)
    {
        $this->spaces[$key] = $value;
    }

    /**
     * @param array $spaces
     * @return void
     */
    public function setSpaces(array $spaces)
    {
        foreach ($spaces as $key => $value) {
            $this->setSpace($key, $value);
        }
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function replaceSeparator($value)
    {
        return str_replace(['/', '\\'], '.', $value);
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function getFromSpace($key)
    {
        [$key, $value] = $this->parseSpace($key);

        return $this->getSpace($key)->get($value);
    }

    /**
     * @param $key
     * @return bool
     */
    public function containSpace($key)
    {
        return strpos($key, '::') !== false;
    }

    /**
     * @param $key
     * @return array
     */
    protected function parseSpace($key)
    {
        return explode('::', $key, 2);
    }
}