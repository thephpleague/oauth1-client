<?php

/**
 * This file is part of the league/oauth1-client library.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Corlett <hello@webcomm.io>
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://thephpleague.com/oauth1-client/ Documentation
 * @link https://packagist.org/packages/league/oauth1-client Packagist
 * @link https://github.com/thephpleague/oauth1-client GitHub
 */

namespace League\OAuth1\Client\Signature;

use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;

abstract class AbstractSignature implements Signature
{
    /**
     * The client credentials.
     *
     * @var ClientCredentials
     */
    protected $clientCredentials;

    /**
     * The (temporary or token) credentials.
     *
     * @var Credentials
     */
    protected $credentials;

    /**
     * Creates signature instance.
     *
     * @param ClientCredentials $clientCredentials
     */
    public function __construct(ClientCredentials $clientCredentials)
    {
        $this->clientCredentials = $clientCredentials;
    }

    /**
     * {@inheritdoc}
     */
    public function setCredentials(Credentials $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * Generate a signing key.
     *
     * @return string
     */
    protected function getKey()
    {
        $key = rawurlencode($this->clientCredentials->getSecret()).'&';

        if (null !== $this->credentials) {
            $key .= rawurlencode($this->credentials->getSecret());
        }

        return $key;
    }
}
