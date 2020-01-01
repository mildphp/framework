<?php

namespace Mild\Config\Loader;

use Mild\Contract\Config\LoaderInterface;

class EnvLoader implements LoaderInterface
{

    /**
     * @param $file
     * @return array
     * @throws LoaderException
     */
    public function load($file)
    {
        $items = [];

        preg_match_all('/(^(?:(?!#)).*(?<!\s))[ \t]*=[ \t]*(.*(?<!\s))/m', $contents = file_get_contents($file), $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        foreach ($matches as $match) {

            if (false === (bool) preg_match('/\A[a-zA-Z0-9_.]+\z/', $key = trim($match[1][0]))) {
                throw new LoaderException(sprintf(
                    'Failed to parse [%s] is invalid key', $key
                ), $file, strlen($normalizeContents = str_split($contents, $match[1][1])[0]) - strlen(str_replace(PHP_EOL, '', $normalizeContents)) + 1);
            }

            preg_match('/\${(.*)}/', $value = trim($match[2][0]), $vars);

            if (!empty($vars)) {
                if (false === (bool) preg_match('/\A[a-zA-Z0-9_.]+\z/', $var = $vars[1])) {
                    throw new LoaderException(sprintf(
                        'Failed to parse [%s] is invalid variable.', $vars[0]
                    ), $file, strlen($normalizeContents = str_split($contents, $match[2][1])[0]) - strlen(str_replace(PHP_EOL, '', $normalizeContents)) + 1);
                }
                if (array_key_exists($var, $items)) {
                    $items[$key] = $items[$var];
                    continue;
                }
            }

            switch (strtolower($value)) {
                case 'true':
                    $value = true;
                    break;
                case 'false':
                    $value = false;
                    break;
                case 'null':
                    $value = null;
                    break;
            }

            $items[$key] = preg_replace('/\'(.*)\'|"(.*)"/', '$1$2', $value);
        }

        return $items;
    }
}