<?php


namespace Mild\Support\Facades;

use Mild\Validation\Validator;
use Mild\Contract\Container\ContainerInterface;

/**
 * Class Validation
 *
 * @package \Mild\Support\Facades
 * @see \Mild\Validation\Factory
 * @method static Validator make($data, $rules, $messages)
 * @method static void rule($key, $value)
 * @method static array getRules()
 * @method static ContainerInterface getContainer()
 * @method static void setRules($rules)
 * @method static void validate($data, $rules, $messages)
 */
class Validation extends Facade
{

    /**
     * @return string|object
     */
    protected static function getAccessor()
    {
        return 'validation';
    }
}