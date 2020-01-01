<?php

namespace Mild\Encryption;

use InvalidArgumentException;
use Mild\Contract\Encryption\EncrypterInterface;

class Encrypter implements EncrypterInterface
{
    /**
     * @var string
     */
    protected $key;
    /**
     * @var string
     */
    protected $cipher;
    /**
     * @var array
     */
    private static $cipherMethods = [
        'AES-128-CBC' => 'AES-128-CBC',
        'AES-128-CBC-HMAC-SHA1' => 'AES-128-CBC-HMAC-SHA1',
        'AES-128-CBC-HMAC-SHA256' => 'AES-128-CBC-HMAC-SHA256',
        'AES-128-CFB' => 'AES-128-CFB',
        'AES-128-CFB1' => 'AES-128-CFB1',
        'AES-128-CFB8' => 'AES-128-CFB8',
        'AES-128-CTR' => 'AES-128-CTR',
        'AES-128-ECB' => 'AES-128-ECB',
        'AES-128-OFB' => 'AES-128-OFB',
        'AES-192-CBC' => 'AES-192-CBC',
        'AES-192-CFB' => 'AES-192-CFB',
        'AES-192-CFB1' => 'AES-192-CFB1',
        'AES-192-CFB8' => 'AES-192-CFB8',
        'AES-192-CTR' => 'AES-192-CTR',
        'AES-192-ECB' => 'AES-192-ECB',
        'AES-192-OFB' => 'AES-192-OFB',
        'AES-256-CBC' => 'AES-256-CBC',
        'AES-256-CBC-HMAC-SHA1' => 'AES-256-CBC-HMAC-SHA1',
        'AES-256-CBC-HMAC-SHA256' => 'AES-256-CBC-HMAC-SHA256',
        'AES-256-CFB' => 'AES-256-CFB',
        'AES-256-CFB1' => 'AES-256-CFB1',
        'AES-256-CFB8' => 'AES-256-CFB8',
        'AES-256-CTR' => 'AES-256-CTR',
        'AES-256-ECB' => 'AES-256-ECB',
        'AES-256-OFB' => 'AES-256-OFB',
        'BF-CBC' => 'BF-CBC',
        'BF-CFB' => 'BF-CFB',
        'BF-ECB' => 'BF-ECB',
        'BF-OFB' => 'BF-OFB',
        'CAMELLIA-128-CBC' => 'CAMELLIA-128-CBC',
        'CAMELLIA-128-CFB' => 'CAMELLIA-128-CFB',
        'CAMELLIA-128-CFB1' => 'CAMELLIA-128-CFB1',
        'CAMELLIA-128-CFB8' => 'CAMELLIA-128-CFB8',
        'CAMELLIA-128-CTR' => 'CAMELLIA-128-CTR',
        'CAMELLIA-128-ECB' => 'CAMELLIA-128-ECB',
        'CAMELLIA-128-OFB' => 'CAMELLIA-128-OFB',
        'CAMELLIA-192-CBC' => 'CAMELLIA-192-CBC',
        'CAMELLIA-192-CFB' => 'CAMELLIA-192-CFB',
        'CAMELLIA-192-CFB1' => 'CAMELLIA-192-CFB1',
        'CAMELLIA-192-CFB8' => 'CAMELLIA-192-CFB8',
        'CAMELLIA-192-CTR' => 'CAMELLIA-192-CTR',
        'CAMELLIA-192-ECB' => 'CAMELLIA-192-ECB',
        'CAMELLIA-192-OFB' => 'CAMELLIA-192-OFB',
        'CAMELLIA-256-CBC' => 'CAMELLIA-256-CBC',
        'CAMELLIA-256-CFB' => 'CAMELLIA-256-CFB',
        'CAMELLIA-256-CFB1' => 'CAMELLIA-256-CFB1',
        'CAMELLIA-256-CFB8' => 'CAMELLIA-256-CFB8',
        'CAMELLIA-256-CTR' => 'CAMELLIA-256-CTR',
        'CAMELLIA-256-ECB' => 'CAMELLIA-256-ECB',
        'CAMELLIA-256-OFB' => 'CAMELLIA-256-OFB',
        'CAST5-CBC' => 'CAST5-CBC',
        'CAST5-CFB' => 'CAST5-CFB',
        'CAST5-ECB' => 'CAST5-ECB',
        'CAST5-OFB' => 'CAST5-OFB',
        'ChaCha20' => 'ChaCha20',
        'ChaCha20-Poly1305' => 'ChaCha20-Poly1305',
        'DES-CBC' => 'DES-CBC',
        'DES-CFB' => 'DES-CFB',
        'DES-CFB1' => 'DES-CFB1',
        'DES-CFB8' => 'DES-CFB8',
        'DES-ECB' => 'DES-ECB',
        'DES-EDE' => 'DES-EDE',
        'DES-EDE-CBC' => 'DES-EDE-CBC',
        'DES-EDE-CFB' => 'DES-EDE-CFB',
        'DES-EDE-OFB' => 'DES-EDE-OFB',
        'DES-EDE3' => 'DES-EDE3',
        'DES-EDE3-CBC' => 'DES-EDE3-CBC',
        'DES-EDE3-CFB' => 'DES-EDE3-CFB',
        'DES-EDE3-CFB1' => 'DES-EDE3-CFB1',
        'DES-EDE3-CFB8' => 'DES-EDE3-CFB8',
        'DES-EDE3-OFB' => 'DES-EDE3-OFB',
        'DES-OFB' => 'DES-OFB',
        'DESX-CBC' => 'DESX-CBC',
        'RC2-40-CBC' => 'RC2-40-CBC',
        'RC2-64-CBC' => 'RC2-64-CBC',
        'RC2-CBC' => 'RC2-CBC',
        'RC2-CFB' => 'RC2-CFB',
        'RC2-ECB' => 'RC2-ECB',
        'RC2-OFB' => 'RC2-OFB',
        'RC4' => 'RC4',
        'RC4-40' => 'RC4-40',
        'RC4-HMAC-MD5' => 'RC4-HMAC-MD5',
        'SEED-CBC' => 'SEED-CBC',
        'SEED-CFB' => 'SEED-CFB',
        'SEED-ECB' => 'SEED-ECB',
        'SEED-OFB' => 'SEED-OFB',
        'aes-128-cbc' => 'aes-128-cbc',
        'aes-128-cbc-hmac-sha1' => 'aes-128-cbc-hmac-sha1',
        'rc2-cfb' => 'rc2-cfb',
        'rc2-ecb' => 'rc2-ecb',
        'aes-128-cfb' => 'aes-128-cfb',
        'aes-128-cfb1' => 'aes-128-cfb1',
        'aes-128-cfb8' => 'aes-128-cfb8',
        'aes-128-ctr' => 'aes-128-ctr',
        'aes-128-ecb' => 'aes-128-ecb',
        'aes-128-ofb' => 'aes-128-ofb',
        'aes-192-cbc' => 'aes-192-cbc',
        'seed-cfb' => 'seed-cfb',
        'aes-192-cfb' => 'aes-192-cfb',
        'aes-192-cfb1' => 'aes-192-cfb1',
        'aes-192-cfb8' => 'aes-192-cfb8',
        'aes-192-ctr' => 'aes-192-ctr',
        'aes-192-ecb' => 'aes-192-ecb',
        'aes-192-ofb' => 'aes-192-ofb',
        'aes-256-cbc' => 'aes-256-cbc',
        'seed-ecb' => 'seed-ecb',
        'rc2-ofb' => 'rc2-ofb',
        'rc4' => 'rc4',
        'rc4-40' => 'rc4-40',
        'rc4-hmac-md5' => 'rc4-hmac-md5',
        'seed-cbc' => 'seed-cbc',
        'aes-256-cfb1' => 'aes-256-cfb1',
        'aes-256-cfb8' => 'aes-256-cfb8',
        'aes-256-ctr' => 'aes-256-ctr',
        'seed-ofb' => 'seed-ofb',
        'aes-256-ecb' => 'aes-256-ecb',
        'aes-256-ofb' => 'aes-256-ofb',
        'bf-cbc' => 'bf-cbc',
        'bf-cfb' => 'bf-cfb',
        'bf-ecb' => 'bf-ecb',
        'bf-ofb' => 'bf-ofb',
        'camellia-128-cbc' => 'camellia-128-cbc',
        'camellia-128-cfb' => 'camellia-128-cfb',
        'camellia-128-cfb1' => 'camellia-128-cfb1',
        'camellia-128-cfb8' => 'camellia-128-cfb8',
        'camellia-128-ctr' => 'camellia-128-ctr',
        'camellia-128-ecb' => 'camellia-128-ecb',
        'camellia-128-ofb' => 'camellia-128-ofb',
        'camellia-192-cbc' => 'camellia-192-cbc',
        'camellia-192-cfb' => 'camellia-192-cfb',
        'camellia-192-cfb1' => 'camellia-192-cfb1',
        'camellia-192-cfb8' => 'camellia-192-cfb8',
        'camellia-192-ctr' => 'camellia-192-ctr',
        'camellia-192-ecb' => 'camellia-192-ecb',
        'camellia-192-ofb' => 'camellia-192-ofb',
        'camellia-256-cbc' => 'camellia-256-cbc',
        'camellia-256-cfb' => 'camellia-256-cfb',
        'camellia-256-cfb1' => 'camellia-256-cfb1',
        'camellia-256-cfb8' => 'camellia-256-cfb8',
        'camellia-256-ctr' => 'camellia-256-ctr',
        'camellia-256-ecb' => 'camellia-256-ecb',
        'camellia-256-ofb' => 'camellia-256-ofb',
        'cast5-cbc' => 'cast5-cbc',
        'cast5-cfb' => 'cast5-cfb',
        'cast5-ecb' => 'cast5-ecb',
        'cast5-ofb' => 'cast5-ofb',
        'chacha20' => 'chacha20',
        'chacha20-poly1305' => 'chacha20-poly1305',
        'des-cbc' => 'des-cbc',
        'des-cfb' => 'des-cfb',
        'des-cfb1' => 'des-cfb1',
        'des-cfb8' => 'des-cfb8',
        'des-ecb' => 'des-ecb',
        'des-ede' => 'des-ede',
        'des-ede-cbc' => 'des-ede-cbc',
        'des-ede-cfb' => 'des-ede-cfb',
        'des-ede-ofb' => 'des-ede-ofb',
        'des-ede3' => 'des-ede3',
        'des-ede3-cbc' => 'des-ede3-cbc',
        'des-ede3-cfb' => 'des-ede3-cfb',
        'des-ede3-cfb1' => 'des-ede3-cfb1',
        'des-ede3-cfb8' => 'des-ede3-cfb8',
        'des-ede3-ofb' => 'des-ede3-ofb',
        'des-ofb' => 'des-ofb',
        'desx-cbc' => 'desx-cbc',
        'rc2-40-cbc' => 'rc2-40-cbc',
        'rc2-64-cbc' => 'rc2-64-cbc',
        'rc2-cbc' => 'rc2-cbc',
        'aes-128-cbc-hmac-sha256' => 'aes-128-cbc-hmac-sha256',
        'aes-256-cbc-hmac-sha1' => 'aes-256-cbc-hmac-sha1',
        'aes-256-cbc-hmac-sha256' => 'aes-256-cbc-hmac-sha256',
        'aes-256-cfb' => 'aes-256-cfb'
    ];

    /**
     * Encrypter constructor.
     *
     * @param $key
     * @param string $cipher
     */
    public function __construct($key, $cipher = 'AES-256-CBC')
    {
        $this->key = $key;
        $this->setCipher($cipher);
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
    public function getCipher()
    {
        return $this->cipher;
    }

    /**
     * @param $cipher
     */
    public function setCipher($cipher)
    {
        if (!is_string($cipher) || !isset(self::$cipherMethods[$cipher])) {
            throw new InvalidArgumentException(sprintf(
                'The cipher is invalid.'
            ));
        }

        $this->cipher = $cipher;
    }

    /**
     * @param $value
     * @return string
     * @throws EncryptionException
     */
    public function encrypt($value)
    {
        if (($value = openssl_encrypt(serialize($value), $this->cipher, $this->key, 0, $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher)))) === false) {
            throw new EncryptionException('Could not encrypt the data.');
        }

        $value = base64_encode(json_encode(['iv' => base64_encode($iv), 'value' => $value]));

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EncryptionException('Could not encrypt the data.');
        }

        return $value;
    }

    /**
     * @param $value
     * @return mixed|string
     * @throws EncryptionException
     */
    public function decrypt($value)
    {
        if (!is_array($value = json_decode(base64_decode($value), true)) && !isset($value['iv']) || !isset($value['value'])) {
            throw new EncryptionException('Could decrypt the data');
        }

        return unserialize(openssl_decrypt($value['value'], $this->cipher, $this->key, 0, base64_decode($value['iv'])));
    }
}