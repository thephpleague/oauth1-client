# OAuth 1.0 Client

[![Latest Stable Version](https://img.shields.io/github/release/thephpleague/oauth1-client.svg?style=flat-square)](https://github.com/thephpleague/oauth1-client/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/thephpleague/oauth1-client/master.svg?style=flat-square&1)](https://travis-ci.org/thephpleague/oauth1-client)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/thephpleague/oauth1-client.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/oauth1-client/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/thephpleague/oauth1-client.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/oauth1-client)
[![Total Downloads](https://img.shields.io/packagist/dt/league/oauth1-client.svg?style=flat-square)](https://packagist.org/packages/thephpleague/oauth1-client)

OAuth 1 Client is an OAuth [RFC 5849 standards-compliant](http://tools.ietf.org/html/rfc5849) library for authenticating against OAuth 1 servers.

Adding support for other providers is trivial. The library requires PHP 5.5+ and is PSR-2 compatible.

### Third-Party Providers

If you would like to support other providers, please make them available as a Composer package, then link to them
below.

These providers allow integration with other providers not supported by `oauth1-client`. They may require an older version
so please help them out with a pull request if you notice this.

- [Intuit](https://packagist.org/packages/wheniwork/oauth1-intuit)

#### Terminology (as per the RFC 5849 specification):

    client
        An HTTP client (per [RFC2616]) capable of making OAuth-
        authenticated requests (Section 3).

    server
        An HTTP server (per [RFC2616]) capable of accepting OAuth-
        authenticated requests (Section 3).

    protected resource
        An access-restricted resource that can be obtained from the
        server using an OAuth-authenticated request (Section 3).

    resource owner
        An entity capable of accessing and controlling protected
        resources by using credentials to authenticate with the server.

    credentials
        Credentials are a pair of a unique identifier and a matching
        shared secret.  OAuth defines three classes of credentials:
        client, temporary, and token, used to identify and authenticate
        the client making the request, the authorization request, and
        the access grant, respectively.

    token
        A unique identifier issued by the server and used by the client
        to associate authenticated requests with the resource owner
        whose authorization is requested or has been obtained by the
        client.  Tokens have a matching shared-secret that is used by
        the client to establish its ownership of the token, and its
        authority to represent the resource owner.

    The original community specification used a somewhat different
    terminology that maps to this specifications as follows (original
    community terms provided on left):

    Consumer:  client

    Service Provider:  server

    User:  resource owner

    Consumer Key and Secret:  client credentials

    Request Token and Secret:  temporary credentials

    Access Token and Secret:  token credentials


## Install

Via Composer

```bash
composer require league/oauth1-client
```


## Usage

### Authenticating with OAuth 1.0

```php
// Create a server instance.
$server = new \League\OAuth1\Client\Server\GenericServer([
    'identifier'              => 'your-identifier',
    'secret'                  => 'your-secret',
    'callbackUri'             => 'http://your-callback-uri/',
    // The following are only required for use with GenericServer
    'responseType'            => 'json', // Optional, defaults to 'json'
    'temporaryCredentialsUri' => 'http://your.service/oauth/temporary-credentials',
    'authorizationUri'        => 'http://your.service/oauth/authorize',
    'tokenCredentialsUri'     => 'http://your.service/oauth/token-credentials',
    'resourceOwnerDetailsUri' => 'http://your.service/me',
]);

// Obtain Temporary Credentials and User Authorization
if (!isset($_GET['oauth_token'], $_GET['oauth_verifier'])) {

    // First part of OAuth 1.0 authentication is to
    // obtain Temporary Credentials.
    $temporaryCredentials = $server->getTemporaryCredentials();

    // Store credentials in the session, we'll need them later
    $_SESSION['temporary_credentials'] = serialize($temporaryCredentials);
    session_write_close();

    // Second part of OAuth 1.0 authentication is to obtain User Authorization
    // by redirecting the resource owner to the login screen on the server.
    // Create an authorization url.
    $authorizationUrl = $server->getAuthorizationUrl($temporaryCredentials);

    // Redirect the user to the authorization URL. The user will be redirected
    // to the familiar login screen on the server, where they will login to
    // their account and authorize your app to access their data.
    header('Location: ' . $authorizationUrl);
    exit;

// Obtain Token Credentials
} else {

    try {

        // Retrieve the temporary credentials we saved before.
        $temporaryCredentials = unserialize($_SESSION['temporary_credentials']);

        // We will now obtain Token Credentials from the server.
        $tokenCredentials = $server->getTokenCredentials(
            $temporaryCredentials,
            $_GET['oauth_token'],
            $_GET['oauth_verifier']
        );

        // We have token credentials, which we may use in authenticated
        // requests against the service provider's API.
        echo $tokenCredentials->getIdentifier() . "\n";
        echo $tokenCredentials->getSecret() . "\n";

        // Using the access token, we may look up details about the
        // resource owner.
        $resourceOwner = $server->getResourceOwner($tokenCredentials);

        var_export($resourceOwner->toArray());

        // The server provides a way to get an authenticated API request for
        // the service, using the access token; it returns an object conforming
        // to Psr\Http\Message\RequestInterface.
        $request = $server->getAuthenticatedRequest(
            'GET',
            'http://your.service/endpoint',
            $tokenCredentials
        );

    } catch (\League\OAuth1\Client\Exceptions\Exception $e) {

        // Failed to get the token credentials or user details.
        exit($e->getMessage());

    }

}
```

### Obtaining Temporary Credentials

The first step to authenticating with OAuth 1 is to obtain temporary credentials. These have been referred to as **request tokens** in earlier versions of OAuth 1.

To do this, we'll obtain and store temporary credentials in the session, and redirect the user to the server:

```php
// Obtain temporary credentials
$temporaryCredentials = $server->getTemporaryCredentials();

// Store credentials in the session, we'll need them later
$_SESSION['temporary_credentials'] = serialize($temporaryCredentials);
session_write_close();

// Create an authorization url.
$authorizationUrl = $server->getAuthorizationUrl($temporaryCredentials);

// Redirect the user to the authorization URL.
header('Location: ' . $authorizationUrl);
exit;
```

The user will be redirected to the familiar login screen on the server, where they will login to their account and authorise your app to access their data.

### Obtaining Token Credentials

Once the user has authenticated (or denied) your application, they will be redirected to the `callbackUri` which you specified when creating the server.

> Note, some servers (such as Twitter) require that the callback URI you specify when authenticating matches what you registered with their app. This is to stop a potential third party impersonating you. This is actually part of the protocol however some servers choose to ignore this.
>
> Because of this, we actually require you specify a redirect URI for all servers, regardless of whether the server requires it or not. This is good practice.

You'll need to handle when the user is redirected back. This will involve obtaining token credentials, which you may then use to make calls to the server on behalf of the user. These have been referred to as **access tokens** in earlier versions of OAuth 1.

```php
if (isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])) {
    // Retrieve the temporary credentials we saved before
    $temporaryCredentials = unserialize($_SESSION['temporary_credentials']);

    // We will now obtain token credentials from the server
    $tokenCredentials = $server->getTokenCredentials($temporaryCredentials, $_GET['oauth_token'], $_GET['oauth_verifier']);
}
```

Now, you may choose to do what you need with the token credentials. You may store them in a database, in the session, or use them as one-off and then forget about them.

All credentials, (`client credentials`, `temporary credentials` and `token credentials`) all implement `League\OAuth1\Client\Credentials\CredentialsInterface` and have two sets of setters and getters exposed:

```php
var_dump($tokenCredentials->getIdentifier());
var_dump($tokenCredentials->getSecret());
```

In earlier versions of OAuth 1, the token credentials identifier and token credentials secret were referred to as **access token** and  **access token secret**. Don't be scared by the new terminology here - they are the same. This package is using the exact terminology in the RFC 5849 OAuth 1 standard.

> Twitter will send back an error message in the `denied` query string parameter, allowing you to provide feedback. Some servers do not send back an error message, but rather do not provide the successful `oauth_token` and `oauth_verifier` parameters.

### Accessing User Information

Now you have token credentials stored somewhere, you may use them to make calls against the server, as an authenticated user.

While this package is not intended to be a wrapper for every server's API, it does include basic methods that you may use to retrieve limited information. An example of where this may be useful is if you are using social logins, you only need limited information to confirm who the user is.

```php
// Using the access token, we may look up details about the
// resource owner, returning a \League\OAuth1\Client\Server\ResourceOwnerInterface instance.
$resourceOwner = $server->getResourceOwner($tokenCredentials);

var_export($resourceOwner->toArray());

// The server provides a way to get an authenticated API request for
// the service, using the access token; it returns an object conforming
// to Psr\Http\Message\RequestInterface.
$request = $server->getAuthenticatedRequest(
    'GET',
    'http://your.service/endpoint',
    $tokenCredentials
);
```

## Testing

### Running automated tests

``` bash
$ composer run-script test
```

## Contributing

Please see [CONTRIBUTING](https://github.com/thephpleague/oauth1-client/blob/master/CONTRIBUTING.md) for details.


## Credits

- [Ben Corlett](https://github.com/bencorlett)
- [Steven Maguire](https://github.com/stevenmaguire)
- [All Contributors](https://github.com/thephpleague/oauth1-client/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/thephpleague/oauth1-client/blob/master/LICENSE) for more information.
