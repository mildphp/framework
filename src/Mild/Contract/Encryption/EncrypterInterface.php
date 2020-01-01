<?php

namespace Mild\Contract\Encryption;

interface EncrypterInterface
{
    /**
     * @return string
     */
    public function getKey();

    /**
     * @return string
     */
    public function getCipher();

    /**
     * @param $cipher
     * @return void
     */
    public function setCipher($cipher);

    /**
     * @param $value
     * @return string
     * @throws EncryptionExceptionInterface
     */
    public function encrypt($value);

    /**
     * @param $value
     * @return string
     * @throws EncryptionExceptionInterface
     */
    public function decrypt($value);
}