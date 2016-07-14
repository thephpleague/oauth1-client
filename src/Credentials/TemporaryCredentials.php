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

namespace League\OAuth1\Client\Credentials;

use League\OAuth1\Client\Exception\ConfigurationException;
use League\OAuth1\Client\Exception\CredentialsException;
use Psr\Http\Message\ResponseInterface;

class TemporaryCredentials extends Credentials
{
    /**
     * Creates temporary credentials from the body response.
     *
     * @param Psr\Http\Message\ResponseInterface $response
     *
     * @return TemporaryCredentials
     *
     * @throws League\OAuth1\Client\Exception\CredentialsException
     */
    public static function createFromResponse(ResponseInterface $response)
    {
        parse_str($response->getBody(), $data);

        if (!$data || !is_array($data)) {
            throw CredentialsException::responseParseError('temporary');
        }

        if (!isset($data['oauth_callback_confirmed']) || $data['oauth_callback_confirmed'] != 'true') {
            $customMessage = isset($data['error']) ? $data['error'] : null;

            throw CredentialsException::temporaryCredentialsRetrievalError($customMessage);
        }

        return new static(
            $data['oauth_token'],
            $data['oauth_token_secret']
        );
    }

    /**
     * Compares a given identifier with the internal identifier. Throws exception
     * if not equal in comparison.
     *
     * @param string $identifier
     *
     * @throws League\OAuth1\Client\Exception\ConfigurationException
     */
    public function checkIdentifier($identifier)
    {
        if ($identifier !== $this->getIdentifier()) {
            throw ConfigurationException::temporaryIdentifierMismatch();
        }
    }
}
