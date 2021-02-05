<?php

namespace League\OAuth1\Client\Credentials;

use RuntimeException;

class RsaKeyPair
{
    /** @var string */
    private $publicKeyPath;

    /** @var string */
    private $privateKeyPath;

    /** @var string|null */
    private $passphrase;

    /** @var RsaKey|null */
    private $publicKey;

    /** @var RsaKey|null */
    private $privateKey;

    public function __construct(string $publicKeyPath, string $privateKeyPath, string $passphrase = null)
    {
        $this->publicKeyPath  = $publicKeyPath;
        $this->privateKeyPath = $privateKeyPath;
        $this->passphrase     = $passphrase;
    }

    public function getPublicKey(): RsaKey
    {
        if (null === $this->publicKey) {
            $publicKey = openssl_pkey_get_public(sprintf('file://%s', $this->publicKeyPath));

            if (false === $publicKey) {
                throw new RuntimeException(sprintf(
                    'Unable to open public key at path "%s".',
                    $this->publicKeyPath
                ));
            }

            $this->publicKey = new RsaKey($publicKey);
        }

        return $this->publicKey;
    }

    public function getPrivateKey(): RsaKey
    {
        if (null === $this->privateKey) {
            $privateKey = openssl_pkey_get_private(
                sprintf('file://%s', $this->privateKeyPath),
                $this->passphrase ?? ''
            );

            if (false === $privateKey) {
                throw new RuntimeException(sprintf(
                    'Unable to open private key at path "%s".',
                    $this->privateKeyPath
                ));
            }

            $this->privateKey = new RsaKey($privateKey);
        }

        return $this->privateKey;
    }

    public function __destruct()
    {
        if ( ! is_null($this->publicKey)) {
            openssl_free_key($this->publicKey->getRaw());
        }

        if ( ! is_null($this->privateKey)) {
            openssl_free_key($this->privateKey->getRaw());
        }
    }
}
