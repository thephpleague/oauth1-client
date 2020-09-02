<?php

namespace League\OAuth1\Client\Tests\Provider;

use League\OAuth1\Client\ParametersBuilder;
use League\OAuth1\Client\Provider\BaseProvider;
use League\OAuth1\Client\RequestInjector;
use League\OAuth1\Client\Signature\Signer;
use Mockery;

trait PreparesRequestInjectorMockInIsolation
{
    private function prepareRequestInjectorMockInIsolation(BaseProvider $provider): RequestInjector
    {
        $parametersBuilder = Mockery::spy(ParametersBuilder::class);

        $provider->resolveParametersBuilderUsing(static function () use ($parametersBuilder): ParametersBuilder {
            return $parametersBuilder;
        });

        $signer = Mockery::spy(Signer::class);

        $provider->resolveSignerUsing(static function () use ($signer): Signer {
            return $signer;
        });

        $requestInjector = Mockery::mock(RequestInjector::class);

        $provider->resolveRequestInjectorUsing(static function () use ($requestInjector): RequestInjector {
            return $requestInjector;
        });

        return $requestInjector;
    }
}