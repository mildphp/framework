<?php

namespace Mild\View;

use Throwable;
use InvalidArgumentException;
use Mild\Support\Traits\Macroable;
use Mild\Contract\View\EngineInterface;
use Mild\Contract\View\CompilerInterface;

class Engine implements EngineInterface
{
    use Macroable;

    /**
     * @var string
     */
    protected $file;
    /**
     * @var array
     */
    protected $data;
    /**
     * @var CompilerInterface
     */
    protected $compiler;
    /**
     * @var array
     */
    protected $sections;
    /**
     * @var array
     */
    private $sectionStacks = [];

    /**
     * View constructor.
     *
     * @param CompilerInterface $compiler
     * @param $file
     * @param array $data
     * @param array $sections
     */
    public function __construct(CompilerInterface $compiler, $file, $data = [], $sections = [])
    {
        $this->compiler = $compiler;
        if (!is_file($file)) {
            throw new InvalidArgumentException(sprintf(
                'File %s does not exists.', $file
            ));
        }
        $this->file = $file;
        $this->setData($data);
        $this->setSections($sections);
    }


    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return CompilerInterface
     */
    public function getCompiler()
    {
        return $this->compiler;
    }

    /**
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * @param $key
     * @param null $default
     * @return string
     */
    public function getSection($key, $default = null)
    {
        if (!isset($this->sections[$key])) {
            return $default;
        }
        return $this->sections[$key];
    }

    /**
     * @param array $sections
     * @return void
     */
    public function setSections(array $sections)
    {
        $this->sections = $sections;
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function setSection($key, $value)
    {
        $this->sections[$key] = $value;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return void
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param array $data
     * @return void
     */
    public function addData(array $data)
    {
        $this->data = $data + $this->data;
    }

    /**
     * @return string
     * @throws Throwable
     */
    public function render()
    {
        ob_start();

        $__level = ob_get_level();

        extract($this->data, EXTR_SKIP);

        try {
            include ''.$this->compiledFile().'';
        } catch (Throwable $e) {

            while (ob_get_level() > $__level) {
                ob_end_clean();
            }

            throw $e;
        }

        return trim(ob_get_clean());
    }

    /**
     * @param $key
     * @param null $value
     * @return void
     */
    protected function startSection($key, $value = null)
    {
        ob_start();
        $this->sectionStacks[] = $key;
        $this->setSection($key, $value);
    }

    /**
     * @return void
     */
    protected function endSection()
    {
        $key = array_pop($this->sectionStacks);
        $this->sections[$key] .= ob_get_clean();
    }

    /**
     * @return string
     */
    protected function compiledFile()
    {
        file_put_contents($__file = $this->getCompiledFileName(), $this->compiler->compile(file_get_contents($this->file)));
        return $__file;
    }

    protected function getCompiledFileName()
    {
        return $this->compiler->getPath().DIRECTORY_SEPARATOR.sha1($this->file).'.php';
    }
}