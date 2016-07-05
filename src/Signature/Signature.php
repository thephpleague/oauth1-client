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

interface Signature
{
    /**
     * Create a new signature instance.
     *
     * @param ClientCredentials $clientCredentials
     */
    public function __construct(ClientCredentials $clientCredentials);

    /**
     * Set the credentials used in the signature. These can be temporary
     * credentials when getting token credentials during the OAuth
     * authentication process, or token credentials when querying
     * the API.
     *
     * @param Credentials $credentials
     */
    public function setCredentials(Credentials $credentials);

    /**
     * Get the OAuth signature method.
     *
     * @return string
     */
    public function getMethod();

    /**
     * Sign the given request for the client.
     *
     * @param string $uri
     * @param array  $parameters
     * @param string $method
     *
     * @return string
     */
    public function sign($uri, array $parameters = array(), $method = 'POST');
}
