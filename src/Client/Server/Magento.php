<?php

namespace League\OAuth1\Client\Server;

use InvalidArgumentException;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Signature\SignatureInterface;
use UnexpectedValueException;

/**
 * Magento OAuth 1.0a.
 * This class reflects two Magento oddities:
 *  - Magento expects the oauth_verifier to be located in the header instead of
 *    the post body.
 *  - Magento expects the Accept to be located in the header
 * Additionally, this is initialized with two additional parameters:
 *  - Boolean 'admin' to use the admin vs customer
 *  - String 'host' with the path to the magento host
 */
class Magento extends Server
{
    /**
     * Admin url.
     *
     * @var string
     */
    protected $adminUrl;

    /**
     * Base uri.
     *
     * @var string
     */
    protected $baseUri;

    /**
     * Server is admin.
     *
     * @var bool
     */
    protected $isAdmin = false;

    /**
     * oauth_verifier stored for use with.
     *
     * @var string
     */
    private $verifier;

    /**
     * {@inheritDoc}
     */
    public function __construct($clientCredentials, SignatureInterface $signature = null)
    {
        parent::__construct($clientCredentials, $signature);
        if (\is_array($clientCredentials)) {
            $this->parseConfigurationArray($clientCredentials);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function urlTemporaryCredentials(): string
    {
        return $this->baseUri . '/oauth/initiate';
    }

    /**
     * {@inheritDoc}
     */
    public function urlAuthorization(): string
    {
        return $this->isAdmin
            ? $this->adminUrl
            : $this->baseUri . '/oauth/authorize';
    }

    /**
     * {@inheritDoc}
     */
    public function urlTokenCredentials(): string
    {
        return $this->baseUri . '/oauth/token';
    }

    /**
     * {@inheritDoc}
     */
    public function urlUserDetails(): string
    {
        return $this->baseUri . '/api/rest/customers';
    }

    /**
     * {@inheritDoc}
     */
    public function userDetails($data, TokenCredentials $tokenCredentials): User
    {
        if (!\is_array($data) || !\count($data)) {
            throw new UnexpectedValueException('Not possible to get user info');
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
            if (!isset($data[$dataKey])) {
                continue;
            }
            $user->{$userKey} = $data[$dataKey];
        }

        $user->extra = array_diff_key($data, array_flip($mapping));

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        return key($data);
    }

    /**
     * {@inheritDoc}
     */
    public function userEmail($data, TokenCredentials $tokenCredentials): ?string
    {
        $data = current($data);
        if (!isset($data['email'])) {
            return null;
        }

        return $data['email'];
    }

    /**
     * {@inheritDoc}
     */
    public function userScreenName($data, TokenCredentials $tokenCredentials): ?string
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getTokenCredentials(TemporaryCredentials $temporaryCredentials, $temporaryIdentifier, $verifier): TokenCredentials
    {
        $this->verifier = $verifier;

        return parent::getTokenCredentials($temporaryCredentials, $temporaryIdentifier, $verifier);
    }

    /**
     * {@inheritDoc}
     */
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
     * @param array $configuration
     */
    private function parseConfigurationArray(array $configuration = []): void
    {
        if (!isset($configuration['host'])) {
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
        $this->isAdmin = !empty($configuration['admin']);
        if (!empty($configuration['adminUrl'])) {
            $this->adminUrl = $configuration['adminUrl'] . '/oauth_authorize';
        } else {
            $this->adminUrl = $this->baseUri . '/admin/oauth_authorize';
        }
    }
}
