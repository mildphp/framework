<?php

namespace Mild\Database\Entity;

use Closure;
use Carbon\Carbon;
use RuntimeException;
use JsonSerializable;
use Mild\Support\Arr;
use Mild\Support\Str;
use Mild\Support\Collection;
use Mild\Database\Connection;
use Mild\Database\Entity\Relations\HasMany;
use Mild\Database\Entity\Relations\Relation;
use Mild\Database\Entity\Relations\BelongsTo;
use Mild\Contract\Container\ContainerInterface;
use Mild\Database\Exceptions\RelationNotFoundException;

/**
 * Class Model
 *
 * @package Mild\Database\Entity
 * @method static Builder with(string|array $relation)
 * @method static Builder without(string|array $relation)
 * @method static static find($id)
 * @method static Collection get()
 * @method static static first()
 * @method static bool delete($id = null)
 * @method static bool save()
 * @method static create(array $attributes = [])
 * @method static bool chunk($count, callable $callback)
 * @method static Builder select(...$columns)
 * @method static Builder where(string $column, string|mixed $operator = '=', string|null $value = null, string $boolean = 'and')
 * @method static Builder orWhere(string $column, string|mixed $operator = '=', string|null $value = null)
 * @method static Builder whereNested(Closure $closure, $boolean = 'and')
 * @method static Builder orWhereNested($closure)
 * @method static Builder whereNull($columns, $boolean = 'and')
 * @method static Builder orWhereNull($columns)
 * @method static Builder whereNotNull($columns, $boolean = 'and')
 * @method static Builder orWhereNotNull($columns)
 * @method static Builder whereIn(string $column, array $values, string $boolean = 'and')
 * @method static Builder orWhereIn(string $column , array $values)
 * @method static Builder whereNotIn($columns, $values, $boolean = 'and')
 * @method static Builder orWhereNotIn($columns, $values)
 * @method static Builder whereBetween($columns, $min, $max, $boolean = 'and')
 * @method static Builder orWhereBetween($columns, $min, $max)
 * @method static Builder whereNotBetween($columns, $min, $max, $boolean = 'and')
 * @method static Builder orWhereNotBetween($columns, $min, $max)
 * @method static Builder whereColumn($first, $operator = null, $second = null, $boolean = 'and')
 * @method static Builder orWhereColumn($first, $operator = null, $second = null)
 * @method static Builder whereRaw($raws, $bindings = [], $boolean = 'and')
 * @method static Builder orWhereRaw($raws, $bindings = [])
 * @method static Builder orderBy($columns, $direction = 'asc')
 * @method static Builder orderByRaw($raw, $bindings = [])
 * @method static Builder groupBy(...$columns)
 * @method static Builder having($columns, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder orHaving($columns, $operator = null, $value = null)
 * @method static Builder havingBetween($columns, $min, $max, $boolean = 'and')
 * @method static Builder orHavingBetween($columns, $min, $max)
 * @method static Builder havingNotBetween($columns, $min, $max, $boolean = 'and')
 * @method static Builder orHavingNotBetween($columns, $min, $max)
 * @method static Builder havingRaw($raws, $bindings = [], $boolean = 'and')
 * @method static Builder orHavingRaw($raws, $bindings = [])
 * @method static Builder union($query, $all = false)
 * @method static Builder unionAll($query)
 * @method static Builder limit(int $limit)
 * @method static Builder offset(int $offset)
 * @method static int insertGetId(array $attributes)
 * @method static bool update(array $attributes = [])
 * @method static string toSql()
 * @method static array getBindings()
 * @method static Builder selectSub($query, $as)
 * @method static Builder selectRaw($raw, $bindings = [])
 * @method static Builder distinct()
 * @method static int count(...$columns)
 * @method static mixed|null min(...$columns)
 * @method static mixed|null max(...$columns)
 * @method static mixed|null sum(...$columns)
 * @method static mixed|null avg(...$columns)
 * @method static mixed|null average(...$columns)
 * @method static mixed|null aggregate($function, $columns = ['*'])
 * @method static bool exists()
 * @method static Builder lock($value = true)
 * @method static Builder lockForUpdate()
 * @method static Builder sharedLock()
 * @method static bool insert($values)
 * @method static bool insertOrIgnore($values)
 * @method static bool insertOrIgnoreGetId($values, $name = null)
 * @method static bool increment($columns, int $value = 1, array $extra = [])
 * @method static bool decrement($columns, int $value = 1, array $extra = [])
 */
abstract class Model implements JsonSerializable
{
    /**
     * @var string
     */
    protected $table;
    /**
     * @var array
     */
    protected $with = [];
    /**
     * @var string
     */
    protected $key = 'id';
    /**
     * @var string
     */
    protected $foreignKey;
    /**
     * @var array
     */
    protected $relations = [];
    /**
     * @var array
     */
    protected $attributes = [];
    /**
     * @var array
     */
    protected $dateAttributes = [
        'created_at' => 'Y-m-d H:i:s',
        'updated_at' => 'Y-m-d H:i:s'
    ];
    /**
     * @var ContainerInterface
     */
    protected static $container;
    /**
     * @var array
     */
    private $resolvedAttributes = [];
    /**
     * @var array
     */
    private static $mutators = [];

    /**
     * Model constructor.
     */
    public function __construct()
    {
        if (!isset(self::$mutators[static::class])) {
            preg_match_all('/get([a-zA-Z0-9_.]+)Attribute(?=;|$)/', implode(';', get_class_methods(static::class)), $matches);
            foreach ($matches[1] as $key => $value) {
                self::$mutators[static::class][Str::snake($value)] = $matches[0][$key];
            }
        }
    }

    /**
     * @return Builder
     */
    public function query()
    {
        return (new Builder($this))->with($this->with);
    }

    /**
     * @return Connection
     */
    public function connection()
    {
        return static::getContainer()->get(Connection::class);
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getForeignKey()
    {
        if (!$this->foreignKey) {
            $this->foreignKey = strtolower(class_basename(static::class)).'_'.$this->key;
        }

        return $this->foreignKey;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasAttribute($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * @param $key
     * @param null $default
     * @return Carbon|mixed|null
     */
    public function getAttribute($key, $default = null)
    {
        if (isset($this->resolvedAttributes[$key])) {
            return $this->resolvedAttributes[$key];
        }

        if (isset(self::$mutators[static::class][$key]) && !Arr::in(self::$mutators[static::class], [($trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3))[1]['function'], $trace[2]['function']])) {
            if (!isset($this->resolvedAttributes[$key])) {
                $this->resolvedAttributes[$key] = static::getContainer()->make([$this, self::$mutators[static::class][$key]], [$this->attributes[$key] ?? null]);
            }

            return $this->getAttribute($key);
        }

        if (!$this->hasAttribute($key)) {
            return $default;
        }

        $value = $this->attributes[$key];

        if (isset($this->dateAttributes[$key]) && !isset($this->resolvedAttributes[$key])) {
            $value = $this->resolvedAttributes[$key] = Carbon::createFromFormat($this->dateAttributes[$key], $value);
        }

        return $value;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setAttribute($key, $value)
    {
        if (method_exists($this, $method = 'set'.Str::studly($key).'Attribute')) {
            static::getContainer()->make([$this, $method], [$value]);
        } else {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * @param $key
     * @return void
     */
    public function putAttribute($key)
    {
        unset($this->attributes[$key], $this->resolvedAttributes[$key]);
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        $attributes = $this->attributes;

        if (isset(self::$mutators[static::class])) {
            foreach (self::$mutators[static::class] as $key => $value) {
                if (!isset($this->resolvedAttributes[$key])) {
                    $this->resolvedAttributes[$key] = static::getContainer()->make([$this, $value], [$attributes[$key] ?? null]);
                }
                $attributes[$key] = $this->resolvedAttributes[$key];
            }
        }

        return $attributes;
    }

    /**
     * @return array
     */
    public function getDateAttributes()
    {
        return $this->dateAttributes;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasRelation($key)
    {
        return array_key_exists($key, $this->relations);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getRelation($key)
    {
        if (!$this->hasRelation($key)) {
            throw new RelationNotFoundException(sprintf(
                'Relation [%s] does not exist.', $key
            ));
        }

        return $this->relations[$key];
    }

    /**
     * @return array
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function setRelation($key, $value)
    {
        $this->relations[$key] = $value;
    }

    /**
     * @param array $relations
     * @return void
     */
    public function setRelations(array $relations)
    {
        $this->relations = $relations;
    }

    /**
     * @param $key
     * @return void
     */
    public function putRelation($key)
    {
        unset($this->relations[$key]);
    }

    /**
     * @param array $attributes
     * @return static
     */
    public function newInstance($attributes = [])
    {
        return static::instance($attributes);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->getAttributes(), $this->getRelations());
    }

    /**
     * @param int $options
     * @param int $depth
     * @return string
     */
    public function toJson($options = 0, $depth = 512)
    {
        return json_encode($this->jsonSerialize(), $options, $depth);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->hasAttribute($name) || $this->hasRelation($name);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (method_exists($this, $name)) {
            if (!$this->hasRelation($name)) {
                $this->setRelation($name, (($relation = static::getContainer()->make([$this, $name])) instanceof Relation) ? $relation->execute() : $relation);
            }

            return $this->getRelation($name);
        }

        return $this->getAttribute($name);
    }

    /**
     * @param $name
     * @param $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    /**
     * @param $name
     * @return void
     */
    public function __unset($name)
    {
        $this->putAttribute($name);
        $this->putRelation($name);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, array $arguments = [])
    {
        return $this->query()->$name(...$arguments);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * @param array $attributes
     * @return static
     */
    public static function instance(array $attributes = [])
    {
        /**
         * @var static $model
         */
        $model = static::getContainer()->make(static::class);

        $model->setAttributes($attributes);

        return $model;
    }

    /**
     * @return ContainerInterface
     */
    public static function getContainer()
    {
        if (null === static::$container) {
            throw new RuntimeException('Container is not set.');
        }

        return static::$container;
    }

    /**
     * @param ContainerInterface $container
     * @return void
     */
    public static function setContainer(ContainerInterface $container)
    {
        static::$container = $container;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, array $arguments = [])
    {
        return static::instance()->__call($name, $arguments);
    }

    /**
     * @param $related
     * @param $foreignKey
     * @param string $primaryKey
     * @return BelongsTo
     */
    protected function belongsTo($related, $primaryKey = '', $foreignKey = '')
    {
        return new BelongsTo($this, $related = $this->resolveModelInstance($related), ($primaryKey = $primaryKey ?: $related->getKey()), $foreignKey ?: (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'].'_'.$primaryKey));
    }

    /**
     * @param $related
     * @param string $primaryKey
     * @param string $foreignKey
     * @return HasMany
     */
    protected function hasMany($related, $primaryKey = '', $foreignKey = '')
    {
        return new HasMany($this, $this->resolveModelInstance($related), $primaryKey ?: $this->key, $foreignKey ?: $this->getForeignKey());
    }

    /**
     * @param self $related
     * @return Model
     */
    protected function resolveModelInstance($related)
    {
        return $related instanceof self ? $related : $related::instance();
    }
}