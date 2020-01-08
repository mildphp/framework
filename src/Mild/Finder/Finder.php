<?php

namespace Mild\Finder;

use SplFileInfo;
use ArrayIterator;
use InvalidArgumentException;
use Mild\Contract\Finder\FinderInterface;
use Mild\Contract\Finder\FilterInterface;

class Finder implements FinderInterface
{
    /**
     * @var string
     */
    protected $path;
    /**
     * @var int
     */
    protected $depth;
    /**
     * @var array
     */
    protected $filters = [];

    /**
     * Finder constructor.
     * @param $path
     * @param $depth
     * @param array $filters
     */
    public function __construct($path, $depth = INF, $filters = [])
    {
        if (!is_dir($path = rtrim($path, '\/'))) {
            throw new InvalidArgumentException(sprintf(
                'Directory %s does not exists', $path
            ));
        }
        $this->path = $path;

        $this->setDepth($depth);

        $this->setFilters($filters);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param FilterInterface $filter
     * @return void
     */
    public function addFilter(FilterInterface $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * @param $depth
     * @return void
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;
    }

    /**
     * @param array $filters
     * @return void
     */
    public function setFilters(array $filters)
    {
        foreach ($this->filters as $filter) {
            $this->addFilter($filter);
        }
    }

    /**
     * @param $filter
     * @return void
     */
    public function setFilter($filter)
    {
        $this->addFilter($filter);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->loadFromPath($this->path));
    }

    /**
     * @param $filter
     * @return $this
     */
    public function filter($filter)
    {
        if ($filter instanceof FilterInterface === false) {
            $filter = new CallableFilter($filter);
        }
        $this->addFilter($filter);
        return $this;
    }

    /**
     * @param $time
     * @param null $operator
     * @return Finder
     */
    public function date($time, $operator = null)
    {
        return $this->filter(new DateFilter($time, $operator));
    }

    /**
     * @param $size
     * @param null $operator
     * @return Finder
     */
    public function size($size, $operator = null)
    {
        return $this->filter(new SizeFilter($size, $operator));
    }

    /**
     * @return Finder
     */
    public function files()
    {
        return $this->filter(new FileFilter);
    }

    /**
     * @return Finder
     */
    public function file()
    {
        return $this->files();
    }

    /**
     * @param $extensions
     * @return Finder
     */
    public function extensions($extensions)
    {
        return $this->filter(new ExtensionFilter($extensions));
    }

    /**
     * @param $extension
     * @return Finder
     */
    public function extension($extension)
    {
        return $this->extensions($extension);
    }

    /**
     * @param $extensions
     * @return Finder
     */
    public function notExtensions($extensions)
    {
        return $this->filter(new NotExtensionFilter($extensions));
    }

    /**
     * @param $extension
     * @return Finder
     */
    public function notExtension($extension)
    {
        return $this->notExtensions($extension);
    }

    /**
     * @return Finder
     */
    public function directories()
    {
        return $this->filter(new DirectoryFilter);
    }

    /**
     * @return Finder
     */
    public function directory()
    {
        return $this->directories();
    }

    /**
     * @return Finder
     */
    public function dirs()
    {
        return $this->directories();
    }

    /**
     * @return Finder
     */
    public function dir()
    {
        return $this->dirs();
    }

    /**
     * @param $needles
     * @return Finder
     */
    public function contains($needles)
    {
        return $this->filter(new ContainFilter($needles));
    }

    /**
     * @param $needle
     * @return Finder
     */
    public function contain($needle)
    {
        return $this->contains($needle);
    }

    /**
     * @param $needles
     * @return Finder
     */
    public function notContains($needles)
    {
        return $this->filter(new NotContainFilter($needles));
    }

    /**
     * @param $needle
     * @return Finder
     */
    public function notContain($needle)
    {
        return $this->notContains($needle);
    }

    /**
     * @param $name
     * @return Finder
     */
    public function name($name)
    {
        return $this->names($name);
    }

    /**
     * @param $names
     * @return Finder
     */
    public function names($names)
    {
        return $this->filter(new NameFilter($names));
    }

    /**
     * @param $name
     * @return Finder
     */
    public function notName($name)
    {
        return $this->notNames($name);
    }

    /**
     * @param $names
     * @return Finder
     */
    public function notNames($names)
    {
        return $this->filter(new NotNameFilter($names));
    }

    /**
     * @param null $names
     * @return Finder
     */
    public function ignoreDotFiles($names = null)
    {
        if ($names) {
            return $this->notNames($names);
        }

        return $this->filter(new DotFileFilter);
    }

    /**
     * @return Finder
     */
    public function readable()
    {
        return $this->filter(new ReadableFilter);
    }

    /**
     * @return Finder
     */
    public function writable()
    {
        return $this->filter(new WritableFilter);
    }

    /**
     * @return Finder
     */
    public function executable()
    {
        return $this->filter(new ExecutableFilter);
    }

    /**
     * @return Finder
     */
    public function link()
    {
        return $this->links();
    }

    /**
     * @return Finder
     */
    public function links()
    {
        return $this->filter(new LinkFilter);
    }

    /**
     * @param $path
     * @param int $depth
     * @param array $filters
     * @return Finder
     */
    public static function instance($path, $depth = INF, $filters = [])
    {
        return new self($path, $depth, $filters);
    }

    /**
     * @param $path
     * @param int $depth
     * @param array $filters
     * @return Finder
     */
    public static function make($path, $depth = INF, $filters = [])
    {
        return self::instance($path, $depth, $filters);
    }

    /**
     * @param $path
     * @param int $depth
     * @param array $filters
     * @return Finder
     */
    public static function create($path, $depth = INF, $filters = [])
    {
        return self::instance($path, $depth, $filters);
    }

    /**
     * @param $path
     * @param int $depth
     * @param array $filters
     * @return Finder
     */
    public static function in($path, $depth = INF, $filters = [])
    {
        return self::instance($path, $depth, $filters);
    }

    /**
     * @param $path
     * @param int $depth
     * @param array $filters
     * @return Finder
     */
    public static function find($path, $depth = INF, $filters = [])
    {
        return self::instance($path, $depth, $filters);
    }

    /**
     * @param $path
     * @return array
     */
    protected function loadFromPath($path)
    {
        $files = [];

        $dirs = [];

        $handle = opendir($path);

        while(false !== ($file = readdir($handle))) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            if ($this->acceptFiltered($file = new SplFileInfo($file = $path.DIRECTORY_SEPARATOR.$file))) {
                $files[] = $file;
            }

            if (is_dir($file)) {
                $dirs[] = $file;
            }
        }

        if ($this->depth > 1) {

            --$this->depth;

            foreach ($dirs as $dir) {
                $files = array_merge($files, $this->loadFromPath($dir));
            }
        }

        closedir($handle);
        return $files;
    }

    /**
     * @param SplFileInfo $spl
     * @return bool
     */
    protected function acceptFiltered($spl)
    {
        foreach ($this->filters as $filter) {
            /**
             * @var FilterInterface $filter
             */
            if ($filter->accept($spl) === false) {
                return false;
            }
        }

        return true;
    }
}
