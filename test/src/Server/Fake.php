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

namespace League\OAuth1\Client\Test\Server;

use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\AbstractServer;
use League\OAuth1\Client\Server\GenericResourceOwner;
use Psr\Http\Message\ResponseInterface;

class Fake extends AbstractServer
{
    protected $name;

    public function getName()
    {
        return $this->name;
    }

    public function setResponseType($responseType)
    {
        $this->responseType = $responseType;

        return $this;
    }

    public function parseResourceOwnersDetailsResponse(ResponseInterface $response)
    {
        parent::parseResourceOwnersDetailsResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    protected function getBaseTemporaryCredentialsUrl()
    {
        return 'http://www.example.com/temporary';
    }

    /**
     * {@inheritdoc}
     */
    protected function getBaseAuthorizationUrl()
    {
        return 'http://www.example.com/authorize';
    }

    /**
     * {@inheritdoc}
     */
    protected function getBaseTokenCredentialsUrl()
    {
        return 'http://www.example.com/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getResourceOwnerDetailsUrl(TokenCredentials $tokenCredentials)
    {
        return 'http://www.example.com/user';
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
