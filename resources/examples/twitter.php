<?php

use GuzzleHttp\Client as HttpClient;
use Http\Factory\Guzzle\RequestFactory;
use League\OAuth1\Client\Client;
use League\OAuth1\Client\Credentials\ClientCredentials;
use League\OAuth1\Client\Credentials\Credentials;
use League\OAuth1\Client\Provider\Twitter;
use Psr\Http\Client\ClientExceptionInterface;

require_once __DIR__ . '/../../vendor/autoload.php';

$provider = new Twitter(new ClientCredentials(
    'your identifier',
    'your secret',
    'http://your-callback-uri/'
));

$client = new Client(
    $provider,
    new RequestFactory(),
    new HttpClient(['timeout' => 30])
);

// Start session
session_start();

// Step 4
if (isset($_GET['user'])) {

    // Check somebody hasn't manually entered this URL in,
    // by checking that we have the token credentials in
    // the session.
    if (!isset($_SESSION['token_credentials'])) {
        echo 'No token credentials.';
        exit(1);
    }

    // Retrieve our token credentials. From here, it's play time!

    /** @var Credentials $tokenCredentials */
    $tokenCredentials = unserialize($_SESSION['token_credentials'], [Credentials::class]);

    // // Below is an example of retrieving the identifier & secret
    // // (formally known as access token key & secret in earlier
    // // OAuth 1.0 specs).
    // $identifier = $tokenCredentials->getIdentifier();
    // $secret = $tokenCredentials->getSecret();

    // Some OAuth clients try to act as an API wrapper for
    // the server and it's API. We don't. This is what you
    // get - the ability to access basic information. If
    // you want to get fancy, you should be grabbing a
    // package for interacting with the APIs, by using
    // the identifier & secret that this package was
    // designed to retrieve for you. But, for fun,
    // here's basic user information.
    $user = $client->fetchUserDetails($tokenCredentials);

    var_dump($user);

// Step 3
} elseif (isset($_GET['oauth_token'], $_GET['oauth_verifier'])) {

    // Retrieve the temporary credentials from step 2
    $temporaryCredentials = unserialize($_SESSION['temporary_credentials'], [Credentials::class]);

    // Third and final part to OAuth 1.0 authentication is to retrieve token
    // credentials (formally known as access tokens in earlier OAuth 1.0
    // specs).
    try {
        $tokenCredentials = $client->fetchTokenCredentials($temporaryCredentials, $_GET['oauth_verifier']);
    } catch (ClientExceptionInterface $e) {
        throw $e;
    }

    // Now, we'll store the token credentials and discard the temporary
    // ones - they're irrelevant at this stage.
    unset($_SESSION['temporary_credentials']);
    $_SESSION['token_credentials'] = serialize($tokenCredentials);
    session_write_close();

    // Redirect to the user page
    header("Location: http://{$_SERVER['HTTP_HOST']}/?user=user");
    exit;

// Step 2.5 - denied request to authorize client
} elseif (isset($_GET['denied'])) {
    echo 'Hey! You denied the client access to your Twitter account! If you did this by mistake, you should <a href="?go=go">try again</a>.';

// Step 2
} elseif (isset($_GET['go'])) {

    // First part of OAuth 1.0 authentication is retrieving temporary credentials.
    // These identify you as a client to the server.
    try {
        $temporaryCredentials = $client->fetchTemporaryCredentials();
    } catch (ClientExceptionInterface $e) {
        throw $e;
    }

    // Second part of OAuth 1.0 authentication is to redirect the
    // resource owner to the login screen on the server.
    $request = $client->prepareAuthorizationRequest($temporaryCredentials);

    // Store the credentials in the session.
    $_SESSION['temporary_credentials'] = serialize($temporaryCredentials);
    session_write_close();

    // Redirect to the authorization request
    header(sprintf('Location: %s', $request->getUri()));

// Step 1
} else {

    // Display link to start process
    echo '<a href="?go=go">Login</a>';
}
