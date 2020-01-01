<?php

namespace Mild\Session;

use ReflectionException;
use Mild\Support\Optional;
use SessionHandlerInterface;
use Mild\Database\Connection;
use Mild\Support\Traits\DatabaseHandlerTrait;
use Mild\Contract\Database\Exceptions\CompilerExceptionInterface;

class DatabaseSessionHandler implements SessionHandlerInterface
{
    use DatabaseHandlerTrait;

    const COL_SESSION_ID = 'id';
    const COL_PAYLOAD = 'payload';
    const COL_LIFETIME = 'lifetime';

    /**
     * @var int
     */
    private $lifetime;

    /**
     * DatabaseSessionHandler constructor.
     *
     * @param Connection $connection
     * @param $table
     * @param array $columns
     * @param $lifetime
     */
    public function __construct(Connection $connection, $table, array $columns, $lifetime)
    {
        $this->connection = $connection;
        $this->table = $table;
        $this->columns = $columns;
        $this->lifetime = $lifetime;
    }

    /**
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * @param string $session_id
     * @return bool
     * @throws CompilerExceptionInterface
     */
    public function destroy($session_id)
    {
        return $this->createQuery()->where($this->getColumn(self::COL_SESSION_ID), '=', $session_id)->delete();
    }

    /**
     * @param int $maxlifetime
     * @return bool
     * @throws CompilerExceptionInterface
     */
    public function gc($maxlifetime)
    {
        return $this->createQuery()->where($this->getColumn(self::COL_LIFETIME), '<=', time())->delete();
    }

    /**
     * @param string $save_path
     * @param string $name
     * @return bool
     */
    public function open($save_path, $name)
    {
        return true;
    }

    /**
     * @param string $session_id
     * @return string
     * @throws CompilerExceptionInterface
     * @throws ReflectionException
     */
    public function read($session_id)
    {
        return $this->getPayload(new Optional($this->createQuery()->where($this->getColumn(self::COL_SESSION_ID), '=', $session_id)->first()));
    }

    /**
     * @param string $session_id
     * @param string $session_data
     * @return bool
     * @throws CompilerExceptionInterface
     */
    public function write($session_id, $session_data)
    {
        $time = time() + $this->lifetime;
        $payload = base64_encode($session_data);
        $colSessId = $this->getColumn(self::COL_SESSION_ID);
        $colPayload = $this->getColumn(self::COL_PAYLOAD);
        $colLifetime = $this->getColumn(self::COL_LIFETIME);

        if ($this->sessionExists($session_id)) {
            return $this->createQuery()->where($colSessId, '=', $session_id)->update([
                $colLifetime => $time,
                $colPayload => $payload
            ]);
        }

        return $this->createQuery()->insert([
            $colLifetime => $time,
            $colPayload => $payload,
            $colSessId => $session_id
        ]);
    }

    /**
     * @param $session_id
     * @return bool
     * @throws CompilerExceptionInterface
     */
    protected function sessionExists($session_id)
    {
        return $this->createQuery()->where($this->getColumn(self::COL_SESSION_ID), '=', $session_id)->exists();
    }

    /**
     * @param Optional $session
     * @return string
     * @throws ReflectionException
     */
    protected function getPayload($session)
    {
        $data = $session->get($this->getColumn(self::COL_PAYLOAD));
        $expiration = $session->get($this->getColumn(self::COL_LIFETIME), 0);

        if ($expiration < time()) {
            return '';
        }

        return base64_decode($data);
    }
}