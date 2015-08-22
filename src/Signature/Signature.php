<?php
/**
 * This file is part of the league/oauth1-client library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Corlett <hello@webcomm.io>
 * @license http://opensource.org/licenses/MIT MIT
 * @link http://thephpleague.com/oauth1-client/ Documentation
 * @link https://packagist.org/packages/league/oauth1-client Packagist
 * @link https://github.com/thephpleague/oauth1-client GitHub
 */

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\CredentialsInterface;

abstract class Signature implements SignatureInterface
{
    /**
     * The client credentials.
     *
     * @var ClientCredentialsInterface
     */
    protected $clientCredentials;

    /**
     * The (temporary or token) credentials.
     *
     * @var CredentialsInterface
     */
    protected $credentials;

    /**
     * {@inheritDoc}
     */
    public function __construct(ClientCredentialsInterface $clientCredentials)
    {
        $this->clientCredentials = $clientCredentials;
    }

    /**
     * {@inheritDoc}
     */
    public function setCredentials(CredentialsInterface $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * Generate a signing key.
     *
     * @return string
     */
    protected function key()
    {
        $key = rawurlencode($this->clientCredentials->getSecret()).'&';

        if ($this->credentials !== null) {
            $key .= rawurlencode($this->credentials->getSecret());
        }

        return $key;
    }
}
