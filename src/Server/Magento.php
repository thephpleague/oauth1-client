<?php

namespace League\OAuth1\Client\Server;

use InvalidArgumentException;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Signature\SignatureInterface;
use LogicException;

/**
 * Magento OAuth 1.0a.
 *
 * This class reflects two Magento oddities:
 *  - Magento expects the oauth_verifier to be located in the header instead of
 *    the post body.
 *  - Magento expects the Accept to be located in the header
 *
 * Additionally, this is initialized with two additional parameters:
 *  - Boolean 'admin' to use the admin vs customer
 *  - String 'host' with the path to the magento host
 */
class Magento extends Server
{
    /** @var string */
    protected $adminUrl;

    /** @var string */
    protected $baseUri;

    /** @var bool */
    protected $isAdmin = false;

    /** @var string */
    private $verifier;

    /**
     * @param ClientCredentials|array $clientCredentials
     */
    public function __construct($clientCredentials, SignatureInterface $signature = null)
    {
        parent::__construct($clientCredentials, $signature);

        if (is_array($clientCredentials)) {
            $this->parseConfigurationArray($clientCredentials);
        }
    }

    public function urlTemporaryCredentials(): string
    {
        return $this->baseUri . '/oauth/initiate';
    }

    public function urlAuthorization(): string
    {
        return $this->isAdmin
            ? $this->adminUrl
            : $this->baseUri . '/oauth/authorize';
    }

    public function urlTokenCredentials(): string
    {
        return $this->baseUri . '/oauth/token';
    }

    public function urlUserDetails(): string
    {
        return $this->baseUri . '/api/rest/customers';
    }

    public function userDetails($data, TokenCredentials $tokenCredentials): User
    {
        if ( ! is_array($data) || empty($data)) {
            throw new LogicException('Not possible to get user info');
        }

        $id = key($data);
        $data = current($data);

        $user = new User();
        $user->uid = $id;

        $mapping = [
            'email' => 'email',
            'firstName' => 'firstname',
            'lastName' => 'lastname',
        ];

        foreach ($mapping as $userKey => $dataKey) {
            if ( ! isset($data[$dataKey])) {
                continue;
            }
            $user->{$userKey} = $data[$dataKey];
        }

        $user->extra = array_diff_key($data, array_flip($mapping));

        return $user;
    }

    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        return key($data);
    }

    public function userEmail($data, TokenCredentials $tokenCredentials): ?string
    {
        $data = current($data);

        return $data['email'] ?? null;
    }

    public function userScreenName($data, TokenCredentials $tokenCredentials):? string
    {
        return null;
    }

    public function getTokenCredentials(
        TemporaryCredentials $temporaryCredentials,
        string $temporaryIdentifier,
        string $verifier
    ): TokenCredentials {
        $this->verifier = $verifier;

        return parent::getTokenCredentials(
            $temporaryCredentials,
            $temporaryIdentifier,
            $verifier
        );
    }

    protected function additionalProtocolParameters(): array
    {
        return [
            'oauth_verifier' => $this->verifier,
        ];
    }

    protected function getHttpClientDefaultHeaders(): array
    {
        $defaultHeaders = parent::getHttpClientDefaultHeaders();

        // Accept header is required, @see Mage_Api2_Model_Renderer::factory
        $defaultHeaders['Accept'] = 'application/json';

        return $defaultHeaders;
    }

    /**
     * Parse configuration array to set attributes.
     *
     * @throws InvalidArgumentException If invalid credentials are passed
     */
    private function parseConfigurationArray(array $configuration = []): void
    {
        if ( ! isset($configuration['host'])) {
            throw new InvalidArgumentException('Missing Magento Host');
        }

        $url = parse_url($configuration['host']);
        $this->baseUri = sprintf('%s://%s', $url['scheme'], $url['host']);

        if (isset($url['port'])) {
            $this->baseUri .= ':' . $url['port'];
        }

        if (isset($url['path'])) {
            $this->baseUri .= '/' . trim($url['path'], '/');
        }
        $this->isAdmin = ! empty($configuration['admin']);
        if ( ! empty($configuration['adminUrl'])) {
            $this->adminUrl = $configuration['adminUrl'] . '/oauth_authorize';
        } else {
            $this->adminUrl = $this->baseUri . '/admin/oauth_authorize';
        }
    }
}
