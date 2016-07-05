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

namespace League\OAuth1\Client\Server;

use League\OAuth1\Client\Credentials\TokenCredentials;
use Psr\Http\Message\ResponseInterface;

class GenericServer extends AbstractServer
{
    /**
     * Temporary credentials url.
     *
     * @var string
     */
    protected $temporaryCredentialsUrl;

    /**
     * Authorization url.
     *
     * @var string
     */
    protected $authorizationUrl;

    /**
     * Token credentials url.
     *
     * @var string
     */
    protected $tokenCredentialsUrl;

    /**
     * Resource owner details url.
     *
     * @var string
     */
    protected $resourceOwnerDetailsUrl;

    /**
     * {@inheritdoc}
     */
    public function getBaseTemporaryCredentialsUrl()
    {
        return $this->temporaryCredentialsUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->authorizationUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTokenCredentialsUrl()
    {
        return $this->tokenCredentialsUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerDetailsUrl(TokenCredentials $tokenCredentials)
    {
        return $this->resourceOwnerDetailsUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function checkResourceOwnerDetailsResponse(ResponseInterface $response, $data)
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function createResourceOwner(array $response, TokenCredentials $tokenCredentials)
    {
        return new GenericResourceOwner($response);
    }
}
