<?php

namespace Mild\Translation;

use Throwable;
use Mild\Support\Str;
use RuntimeException;
use Mild\Finder\Finder;
use Mild\Config\Loader\EnvLoader;
use Mild\Config\Loader\IniLoader;
use Mild\Config\Loader\PhpLoader;
use Mild\Config\Loader\XmlLoader;
use Mild\Config\Loader\YamlLoader;
use Mild\Config\Loader\JsonLoader;
use Mild\Support\Traits\Macroable;
use Mild\Contract\Config\LoaderInterface;

class FileRepository extends Repository
{
    use Macroable;

    /**
     * @var string
     */
    private $path;

    /**
     * FileRepository constructor.
     *
     * @param $path
     * @param array $spaces
     */
    public function __construct($path, $spaces = [])
    {
        $this->path = $path;

        parent::__construct($spaces);
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        // Untuk performance yang baik, kita akan mengambil apa yang di butuhkan oleh anda.
        if (!$this->has($name = ($parts = $this->parseParts($key))['key'])) {
            try {
                foreach (Finder::instance($parts['path'], 1)->names($parts['name'])->files() as $file) {
                    $this->add($name, $this->createLoaderFromExtension($file->getExtension())->load($file));
                }
            } catch (Throwable $e) {
                //
            }
        }

        return parent::get($key, $default);
    }

    /**
     * @return EnvLoader
     */
    public function getEnvLoader()
    {
        return new EnvLoader;
    }

    /**
     * @return XmlLoader
     */
    public function getXmlLoader()
    {
        return new XmlLoader;
    }

    /**
     * @return IniLoader
     */
    public function getIniLoader()
    {
        return new IniLoader;
    }

    /**
     * @return JsonLoader
     */
    public function getJsonLoader()
    {
        return new JsonLoader;
    }

    /**
     * @return YamlLoader
     */
    public function getYamlLoader()
    {
        return new YamlLoader;
    }

    /**
     * @return PhpLoader
     */
    public function getPhpLoader()
    {
        return new PhpLoader;
    }

    /**
     * @param $key
     * @return array
     */
    protected function parseParts($key)
    {
        $parts = $this->parseKey($key);

        $name = array_pop($parts);

        if (!is_dir($path = $this->resolvePathFromParts($parts))) {
            return $this->parseParts(implode('.', $parts));
        }

        return compact('key', 'name', 'path');
    }

    /**
     * @param $parts
     * @return string
     */
    protected function resolvePathFromParts($parts)
    {
        return $this->path.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $parts);
    }

    /**
     * @param $extension
     * @return LoaderInterface
     */
    protected function createLoaderFromExtension($extension)
    {
        if (method_exists($this, $method = sprintf('get%sLoader', Str::studly($extension)))) {
            return $this->$method();
        }

        if (self::hasMacro($extension)) {
            return $this->$extension();
        }

        throw new RuntimeException(sprintf(
            'Cannot load [%s] extension', $extension
        ));
    }
}