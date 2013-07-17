<?php

namespace League\OAuth1\Client\Server;

use Guzzle\Service\Client as GuzzleClient;

abstract class IdentityServer
{
    public $clientId = '';

    public $clientSecret = '';

    public $redirectUri = '';

    public $name;

    public $uidKey = 'uid';

    public $scopes = array();

    public $method = 'post';

    public $scopeSeperator = ',';

    public $responseType = 'json';

    abstract public function urlTemporaryToken();

    public function getTemporaryToken($params = array())
    {
        $request = new Request\TemporaryRequest;

        $defaultParams = array(
            'oauth_consumer_key',
            'oauth_token',
            'oauth_signature_method',
            'oauth_timestamp',
            'oauth_nonce',
            'oauth_version',
        );

        $requestParams = $request->prepRequestParams($defaultParams, $params);

        try {
            $client = new GuzzleClient($this->urlTemporaryToken());
            $request = $client->post(null, nul, $requestParams)->send();
            $response = $request->getBody();
        } catch (\Guzzle\Http\Exception\BadResponseException $e) {
            throw $e;
        }

        var_dump($response);
    }
}