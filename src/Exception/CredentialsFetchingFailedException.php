<?php

namespace League\OAuth1\Client\Exception;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class CredentialsFetchingFailedException extends RuntimeException
{
    private const TEMPORARY_CREDENTIALS_MESSAGE = 'An error occurred fetching temporary credentials.';
    private const TOKEN_CREDENTIALS_MESSAGE     = 'An error occurred fetching token credentials.';

    /** @var ResponseInterface */
    private $response;

    public static function forTemporaryCredentials(ResponseInterface $response): CredentialsFetchingFailedException
    {
        $instance = new static(self::TEMPORARY_CREDENTIALS_MESSAGE);

        $instance->response = $response;

        return $instance;
    }

    public static function forTokenCredentials(ResponseInterface $response): CredentialsFetchingFailedException
    {
        $instance = new static(self::TOKEN_CREDENTIALS_MESSAGE);

        $instance->response = $response;

        return $instance;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}