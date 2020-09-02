<?php

namespace League\OAuth1\Client\Tests\Exception;

use GuzzleHttp\Psr7\Response;
use League\OAuth1\Client\Exception\CredentialsFetchingFailedException;
use PHPUnit\Framework\TestCase;

class CredentialsFetchingFailedExceptionTest extends TestCase
{
    /** @test */
    public function it_works_for_temporary_credentials(): void
    {
        $exception = CredentialsFetchingFailedException::forTemporaryCredentials(
            $response = new Response()
        );

        self::assertStringContainsString('temporary credentials', $exception->getMessage());
        self::assertEquals($response, $exception->getResponse());
    }

    /** @test */
    public function it_works_for_token_credentials(): void
    {
        $exception = CredentialsFetchingFailedException::forTokenCredentials(
            $response = new Response()
        );

        self::assertStringContainsString('token credentials', $exception->getMessage());
        self::assertEquals($response, $exception->getResponse());
    }
}