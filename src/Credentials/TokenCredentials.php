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

use League\OAuth1\Client\Exception\CredentialsException;
use Psr\Http\Message\ResponseInterface;

class TokenCredentials extends Credentials
{
    /**
     * Creates token credentials from a given response.
     *
     * @param ResponseInterface $response
     *
     * @return TokenCredentials
     *
     * @throws CredentialsException
     */
    public static function createFromResponse(ResponseInterface $response)
    {
        parse_str($response->getBody(), $data);

        if (!$data || !is_array($data)) {
            throw CredentialsException::responseParseError('token');
        }

        if (isset($data['error'])) {
            throw CredentialsException::tokenCredentialsRetrievalError($data['error']);
        }

        return new static(
            $data['oauth_token'],
            $data['oauth_token_secret']
        );
    }
}
