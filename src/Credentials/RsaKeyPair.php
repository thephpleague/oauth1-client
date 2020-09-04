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

    /** @var resource|null */
    private $publicKey;

    /** @var resource|null */
    private $privateKey;

    public function __construct(string $publicKeyPath, string $privateKeyPath, string $passphrase = null)
    {
        $this->publicKeyPath  = $publicKeyPath;
        $this->privateKeyPath = $privateKeyPath;
        $this->passphrase     = $passphrase;
    }

    /**
     * @return resource|null
     */
    public function getPublicKey()
    {
        if (null === $this->publicKey) {
            $publicKey = openssl_pkey_get_public(sprintf('file://%s', $this->publicKeyPath));

            if (false === $publicKey) {
                throw new RuntimeException(sprintf(
                    'Unable to open public key at path "%s".',
                    $this->publicKeyPath
                ));
            }

            $this->publicKey = $publicKey;
        }

        return $this->publicKey;
    }

    /**
     * @return resource|null
     */
    public function getPrivateKey()
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

            $this->privateKey = $privateKey;
        }

        return $this->privateKey;
    }

    public function __destruct()
    {
        if (is_resource($this->publicKey)) {
            openssl_free_key($this->publicKey);
        }

        if (is_resource($this->privateKey)) {
            openssl_free_key($this->privateKey);
        }
    }
}
