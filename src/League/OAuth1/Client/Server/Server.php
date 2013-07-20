<?php

namespace League\OAuth1\Client\Server;

use League\OAuth1\Client\Credentials\ClientCredentialsInterface;
use League\OAuth1\Client\Credentials\ClientCredentials;

abstract class Server
{
    protected $clientCredentials;

    public function __construct($options = array())
    {
        // If our options are in fact an instance of client credentials,
        // our options are actually our second argument.
        if ($options instanceof ClientCredentialsInterface) {
            $clientCredentials = $options;

            if (func_num_args() == 2) {
                $options = func_get_arg(1);
            } else {
                $options = array();
            }
        } else {
            list($clientCredentials, $options) = $this->extractClientCredentials($options);
        }

        $this->clientCredentials = $clientCredentials;
    }

    public function getTemporaryToken()
    {
        $dateTime = new DateTime;

        $request = $this->createRequest($this->urlTemporaryToken(), 'POST');

        $headerParams = array_merge($this->baseHeaderParams(), array(
            'oauth_signature' => $this->signature->sign($request, $this),
        ));

        // try {
            $client = $this->createGuzzleClient($request);
            $request = $client->post(null, array(
                'Authorization' => $this->normalizeHeaderParams($headerParams),
            ))->send();
        // } catch (\Guzzle\Http\Exception\BadResponseException $e) {
        //     throw $e;
        // }

        return $this->createTemporaryToken($responseBody);

        // var_dump($response);
    }

    public function authorize($token, $extraParams = null)
    {
        if ($token instanceof TemporaryToken) {
            $token = $token->getToken();
        }

        header('Location: '.$this->urlAuthorize().'?'.http_build_query(array('oauth_token' => $token)));
        exit;
    }

    public function getAccessToken(TemporaryToken $token)
    {
    }

    protected function extractClientCredentials(array $options = array())
    {
        $keys = array('identifier', 'secret', 'callback_uri');

        foreach ($keys as $key) {
            if ( !isset($options[$key])) {
                throw new \InvalidArgumentException("Missing client credentials key [$key] from options.");
            }
        }

        $clientCredentials = new ClientCredentials;
        $clientCredentials->setIdentifier($options['identifier']);
        $clientCredentials->setSecret($options['secret']);
        $clientCredentials->setCallbackUri($options['callback_uri']);

        foreach ($keys as $key) {
            unset($options[$key]);
        }

        return array($clientCredentials, $options);
    }

    protected function createTemporaryToken($response)
    {
        parse_str($response, $data);

        if ( ! $data || ! is_array($data)) {
            throw new \TokenException("Unable to parse response token.");
        }

        if ( ! isset($data['oauth_callback_confirmed']) || $data['oauth_callback_confirmed'] != 'true') {
            throw new \TokenException("Error in retrieving token.");
        }

        $token = new TemporaryToken;
        $token->setToken($data['oauth_token']);
        $token->setSecret($data['oauth_token_secret']);

        return $token;
    }

    protected function baseHeaderParams()
    {
        return array(
            'oauth_consumer_key' => $this->clientId,
            'oauth_signature_method' => 'PLAINTEXT',
            'oauth_timestamp' => $dateTime->format('U'),
            'oauth_nonce' => $this->nonce(),
            'oauth_version' => '1.0',
        );
    }

    protected function normalizeHeaderParams(array $params)
    {
        array_walk($params, function(&$value, $key)
        {
            $value = rawurlencode($key).'="'.rawurlencode($value).'"';
        });

        return 'OAuth '.implode(', ', $params);
    }

    protected function createGuzzleClient(Request $request)
    {
        return new GuzzleClient($request->getUri());
    }

    /**
     * Pseudo random string generator used to build a unique string to sign each request
     *
     * @param int $length
     * @return string
     */
    protected function nonce($length = 32)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

        $nonce = '';
        $maxRand = strlen($characters)-1;
        for($i = 0; $i < $length; $i++) {
            $nonce.= $characters[rand(0, $maxRand)];
        }

        return $nonce;
    }

    abstract public function urlTemporaryCredentials();

    abstract public function urlAuthorization();

    abstract public function urlTokenCredentials();
}