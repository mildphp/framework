<?php

namespace Mild\Translation;

use Mild\Support\Dot;
use InvalidArgumentException;
use Mild\Contract\Translation\RepositoryInterface;

abstract class Repository extends Dot implements RepositoryInterface
{
    /**
     * @var array
     */
    protected $spaces = [];

    /**
     * Repository constructor.
     *
     * @param array $spaces
     */
    public function __construct($spaces = [])
    {
        $this->setSpaces($spaces);
    }

    /**
     * @param $key
     * @return RepositoryInterface
     */
    public function getSpace($key)
    {
        if (!$this->hasSpace($key)) {
            throw new InvalidArgumentException(sprintf(
                'Space [%s] is not defined', $key
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
     * @return array
     */
    public function getSpaces()
    {
        return $this->spaces;
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
}