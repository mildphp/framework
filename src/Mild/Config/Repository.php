<?php

namespace Mild\Config;

use Mild\Support\Dot;
use Mild\Support\Arr;
use Mild\Support\Str;
use RuntimeException;
use Mild\Finder\Finder;
use Mild\Config\Loader\PhpLoader;
use Mild\Config\Loader\JsonLoader;
use Mild\Config\Loader\XmlLoader;
use Mild\Config\Loader\EnvLoader;
use Mild\Config\Loader\IniLoader;
use Mild\Config\Loader\YamlLoader;
use Mild\Support\Traits\Macroable;
use Mild\Contract\Config\LoaderInterface;
use Mild\Contract\Config\RepositoryInterface;
use Mild\Contract\Config\LoaderExceptionInterface;

class Repository extends Dot implements RepositoryInterface
{
    use Macroable;

    /**
     * @param $paths
     * @param string $prefix
     * @throws LoaderExceptionInterface
     */
    public function load($paths, $prefix = '')
    {
        if (!empty($prefix)) {
            $prefix = trim(str_replace(DIRECTORY_SEPARATOR, '.', $prefix), '.');
        }

        foreach (Arr::wrap($paths) as $path) {
            foreach (Finder::instance($path)->files() as $file) {

                $info = pathinfo($file);

                if ($prefix .= trim(str_replace($path, '', $file->getPath()), DIRECTORY_SEPARATOR)) {
                    $prefix = str_replace(DIRECTORY_SEPARATOR, '.', $prefix).'.';
                }

                $this->add($prefix.$info['filename'], $this->createLoaderFromExtension($info['extension'])->load($file->getRealPath()));
            }
        }
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