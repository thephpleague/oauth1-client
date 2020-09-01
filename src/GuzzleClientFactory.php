<?php

namespace League\OAuth1\Client;

use GuzzleHttp\Client as HttpClient;
use Http\Factory\Guzzle\RequestFactory;

class GuzzleClientFactory implements ClientFactory
{
    public function createClient(ClientConfig $clientConfig): Client
    {
        // @todo Investigate if we should use a PSR-11 container…
        $provider = new ($clientConfig->getProvider())();

        $requestFactory = new RequestFactory();

        $httpClient = new HttpClient(
            $clientConfig->getHttpClientOptions()
        );

        return new Client($provider, $requestFactory, $httpClient);
    }
}