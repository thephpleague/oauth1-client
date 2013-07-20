<?php

namespace League\OAuth1\Client\Server;

use DateTime;
use Guzzle\Service\Client as GuzzleClient;
use League\OAuth1\Client\Signature\SignatureInterface;
use League\OAuth1\Client\Signature\HmacSha1Signature;
use League\OAuth1\Client\Signature\PlainTextSignature;
use League\OAuth1\Client\Token\TemporaryToken;
use League\OAuth1\Client\Token\TokenException;
use Symfony\Component\HttpFoundation\Request;

abstract class IdentityServer implements ServerInterface
{
    protected $signature;

    public $clientId = '';

    public $clientSecret = '';

    public $callbackUri = '';

    public $name;

    public $uidKey = 'uid';

    public $scopes = array();

    public $method = 'post';

    public $scopeSeperator = ',';

    public $responseType = 'json';

    public function __construct($options = array(), SignatureInterface $signature = null)
    {
        foreach ($options as $option => $value) {
            if (isset($this->{$option})) {
                $this->{$option} = $value;
            }
        }

        $this->signature = $signature ?: new PlainTextSignature;
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    public function getCallbackUri()
    {
        return $this->callbackUri;
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

    protected function createRequest($url)
    {
        return new Request($url);
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

    abstract public function urlTemporaryToken();

    // abstract public function urlAuthorize();
}