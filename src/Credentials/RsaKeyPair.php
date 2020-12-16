<?php

namespace League\OAuth1\Client\Credentials;

use OpenSSLAsymmetricKey;
use RuntimeException;

class RsaKeyPair
{
    /** @var string */
    private $publicKeyPath;

    /** @var string */
    private $privateKeyPath;

    /** @var string|null */
    private $passphrase;

    /** @var resource|OpenSSLAsymmetricKey|null */
    private $publicKey;

    /** @var resource|OpenSSLAsymmetricKey|null */
    private $privateKey;

    public function __construct(string $publicKeyPath, string $privateKeyPath, string $passphrase = null)
    {
        $this->publicKeyPath  = $publicKeyPath;
        $this->privateKeyPath = $privateKeyPath;
        $this->passphrase     = $passphrase;
    }

    /**
     * @return resource|OpenSSLAsymmetricKey
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
     * @return resource|OpenSSLAsymmetricKey
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
        if ( ! is_null($this->publicKey)) {
            openssl_free_key($this->publicKey);
        }

        if ( ! is_null($this->privateKey)) {
            openssl_free_key($this->privateKey);
        }
    }
}
