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
     * Temporary credentials uri.
     *
     * @var string
     */
    protected $temporaryCredentialsUri;

    /**
     * Authorization uri.
     *
     * @var string
     */
    protected $authorizationUri;

    /**
     * Token credentials uri.
     *
     * @var string
     */
    protected $tokenCredentialsUri;

    /**
     * Resource owner details uri.
     *
     * @var string
     */
    protected $resourceOwnerDetailsUri;

    /**
     * {@inheritdoc}
     */
    protected function getBaseTemporaryCredentialsUrl()
    {
        return $this->temporaryCredentialsUri;
    }

    /**
     * {@inheritdoc}
     */
    protected function getBaseAuthorizationUrl()
    {
        return $this->authorizationUri;
    }

    /**
     * {@inheritdoc}
     */
    protected function getBaseTokenCredentialsUrl()
    {
        return $this->tokenCredentialsUri;
    }

    /**
     * {@inheritdoc}
     */
    protected function getResourceOwnerDetailsUrl(TokenCredentials $tokenCredentials)
    {
        return $this->resourceOwnerDetailsUri;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, TokenCredentials $tokenCredentials)
    {
        $user = new GenericResourceOwner($response);

        return $user;
    }
}
