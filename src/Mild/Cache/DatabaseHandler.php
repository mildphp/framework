<?php

namespace Mild\Cache;

use DateTimeInterface;
use InvalidArgumentException;
use Mild\Database\Connection;
use Mild\Contract\Cache\HandlerInterface;
use Mild\Support\Traits\DatabaseHandlerTrait;
use Mild\Contract\Database\Exceptions\CompilerExceptionInterface;

class DatabaseHandler implements HandlerInterface
{
    use DatabaseHandlerTrait;

    const COL_KEY = 'key';
    const COL_PAYLOAD = 'payload';
    const COL_EXPIRATION = 'expiration';

    /**
     * DatabaseHandler constructor.
     *
     * @param Connection $connection
     * @param $table
     * @param array $columns
     */
    public function __construct(Connection $connection, $table, array $columns = [])
    {
        $this->connection = $connection;
        $this->table = $table;
        $this->columns = $columns;
    }

    /**
     * @param $key
     * @return mixed|null
     * @throws CompilerExceptionInterface
     */
    public function get($key)
    {
        $payload = $this->getPayload($key);

        if ($payload['expiration'] < time()) {
            $this->put($key);
            $payload['value'] = null;
        }

        return $payload['value'];
    }

    /**
     * @param $key
     * @param $value
     * @param mixed $expiration
     * @throws CompilerExceptionInterface
     */
    public function set($key, $value, $expiration)
    {
        if ($expiration instanceof DateTimeInterface) {
            $expiration = $expiration->format('U');
        } elseif (!is_numeric($expiration) && ($expiration = strtotime($expiration)) === false) {
            throw new InvalidArgumentException(sprintf(
                'The expiration time is invalid type.'
            ));
        }

        // Set forever expiration
        if ($expiration == 0) {
            $expiration = time() + 315360000;
        }

        $colKey = $this->getColumn(self::COL_KEY);
        $colPayload = $this->getColumn(self::COL_PAYLOAD);
        $colExpiration = $this->getColumn(self::COL_EXPIRATION);

        if ($this->createQuery()->where($colKey, '=', $key)->exists()) {
            $this->createQuery()->where($colKey, '=', $key)->update([
                $colExpiration => $expiration,
                $colPayload => base64_encode(serialize($value))
            ]);
        } else {
            $this->createQuery()->insert([
                $colKey => $key,
                $colExpiration => $expiration,
                $colPayload => base64_encode(serialize($value))
            ]);
        }
    }

    /**
     * @param $key
     * @throws CompilerExceptionInterface
     */
    public function put($key)
    {
        $this->createQuery()->where($this->getColumn(self::COL_KEY), '=', $key)->delete();
    }

    /**
     * @param $key
     * @param int $value
     * @return int|void
     * @throws CompilerExceptionInterface
     */
    public function increment($key, int $value = 1)
    {
        $payload = $this->getPayload($key);

        $this->set($key, $value = (int) $payload['value'] + $value, $payload['expiration']);

        return $value;
    }

    /**
     * @param $key
     * @param int $value
     * @return int
     * @throws CompilerExceptionInterface
     */
    public function decrement($key, int $value = 1)
    {
        $payload = $this->getPayload($key);

        $this->set($key, $value = (int) $payload['value'] - $value, $payload['expiration']);

        return $value;
    }

    /**
     * @return void
     */
    public function flush()
    {
        $this->createQuery()->delete();
    }

    /**
     * @param $key
     * @return null
     * @throws CompilerExceptionInterface
     */
    protected function getPayload($key)
    {
        $value = null;
        $expiration = 0;

        if (($cache = $this->createQuery()->where($this->getColumn(self::COL_KEY), '=', $key)->first())) {
            $expiration = $cache->{$this->getColumn(self::COL_EXPIRATION)};
            $value = unserialize(base64_decode($cache->{$this->getColumn(self::COL_PAYLOAD)}));
        }

        return compact('value', 'expiration');
    }
}