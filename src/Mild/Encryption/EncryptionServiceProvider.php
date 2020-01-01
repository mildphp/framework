<?php

namespace Mild\Encryption;

use Mild\Support\ServiceProvider;
use Mild\Contract\Encryption\EncrypterInterface;

class EncryptionServiceProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function register()
    {
        $this->application->set('encrypter', function () {
            return new Encrypter('mild');
        });
        $this->application->alias(Encrypter::class, 'encrypter');
        $this->application->alias(EncrypterInterface::class, 'encrypter');
    }
}