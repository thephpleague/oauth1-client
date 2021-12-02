<?php

namespace League\OAuth1\Client\Credentials;

use OpenSSLAsymmetricKey;

class RsaClientCredentials extends ClientCredentials
{
    /**
     * @var string
     */
    protected $rsaPublicKeyFile;

    /**
     * @var string
     */
    protected $rsaPrivateKeyFile;

    /**
     * @var string
     */
    protected $rsaPublicKeyContent;

    /**
     * @var string
     */
    protected $rsaPrivateKeyContent;

    /**
     * @var resource|OpenSSLAsymmetricKey|null
     */
    protected $rsaPublicKey;

    /**
     * @var resource|OpenSSLAsymmetricKey|null
     */
    protected $rsaPrivateKey;

    /**
     * Sets the path to the RSA public key.
     *
     * @param string $filename
     *
     * @return self
     */
    public function setRsaPublicKey($filename)
    {
        $this->rsaPublicKeyFile = $filename;
        $this->rsaPublicKeyContent = null;
        $this->rsaPublicKey = null;

        return $this;
    }

    /**
     * Sets the path to the RSA private key.
     *
     * @param string $filename
     *
     * @return self
     */
    public function setRsaPrivateKey($filename)
    {
        $this->rsaPrivateKeyFile = $filename;
        $this->rsaPrivateKeyContent = null;
        $this->rsaPrivateKey = null;

        return $this;
    }

    /**
     * Sets the RSA public key.
     *
     * @param string $content
     *
     * @return self
     */
    public function setRsaPublicKeyContent($content)
    {
        $this->rsaPublicKeyFile = null;
        $this->rsaPublicKeyContent = $content;
        $this->rsaPublicKey = null;

        return $this;
    }

    /**
     * Sets the RSA private key.
     *
     * @param string $content
     *
     * @return self
     */
    public function setRsaPrivateKeyContent($content)
    {
        $this->rsaPrivateKeyFile = null;
        $this->rsaPrivateKeyContent = $content;
        $this->rsaPrivateKey = null;

        return $this;
    }

    /**
     * Gets the RSA public key.
     *
     * @throws CredentialsException when the key could not be loaded.
     *
     * @return resource|OpenSSLAsymmetricKey
     */
    public function getRsaPublicKey()
    {
        if ($this->rsaPublicKey) {
            return $this->rsaPublicKey;
        }

        if ( empty($this->rsaPublicKeyContent)) {
            if ( ! file_exists($this->rsaPublicKeyFile)) {
                throw new CredentialsException('Could not read the public key file.');
            }

            $this->rsaPublicKeyContent = file_get_contents($this->rsaPublicKeyFile);
        }

        $this->rsaPublicKey = openssl_get_publickey($this->rsaPublicKeyContent);

        if ( ! $this->rsaPublicKey) {
            throw new CredentialsException('Cannot access public key for signing');
        }

        return $this->rsaPublicKey;
    }

    /**
     * Gets the RSA private key.
     *
     * @throws CredentialsException when the key could not be loaded.
     *
     * @return resource|OpenSSLAsymmetricKey
     */
    public function getRsaPrivateKey()
    {
        if ($this->rsaPrivateKey) {
            return $this->rsaPrivateKey;
        }

        if ( empty($this->rsaPrivateKeyContent)) {
            if ( ! file_exists($this->rsaPrivateKeyFile)) {
                throw new CredentialsException('Could not read the private key file.');
            }

            $this->rsaPrivateKeyContent = file_get_contents($this->rsaPrivateKeyFile);
        }

        $this->rsaPrivateKey = openssl_pkey_get_private($this->rsaPrivateKeyContent);

        if ( ! $this->rsaPrivateKey) {
            throw new CredentialsException('Cannot access private key for signing');
        }

        return $this->rsaPrivateKey;
    }
}
