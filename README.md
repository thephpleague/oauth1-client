oauth1
======

OAuth 1 Client is an OAuth [RFC 5849 standards-compliant](http://tools.ietf.org/html/rfc5849) library for authenticating against OAuth 1 servers.


#### Terminology (as per specification):

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