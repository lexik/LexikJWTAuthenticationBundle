Functionally testing a JWT protected api
========================================

Configuration
-------------

Generate some test specific keys, for example:

.. code-block:: terminal

    $ openssl genrsa -out config/jwt/private-test.pem -aes256 4096
    $ openssl rsa -pubout -in config/jwt/private-test.pem -out config/jwt/public-test.pem

Override the bundle configuration in your ``config_test.yml`` :

.. code-block:: yaml

    # config/test/lexik_jwt_authentication.yaml
    lexik_jwt_authentication:
        secret_key: '%kernel.project_dir%/config/jwt/private-test.pem'
        public_key: '%kernel.project_dir%/config/jwt/public-test.pem'

**Protip:** You might want to commit those keys if you intend to run
your test on a CI server.

Usage
-----

Create an authenticated client:

.. code-block:: php

    /**
     * Create a client with a default Authorization header.
     *
     * @param string $username
     * @param string $password
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function createAuthenticatedClient($username = 'user', $password = 'password')
    {
        $client = static::createClient();
        $client->request(
          'POST',
          '/api/login_check',
          [],
          [],
          ['CONTENT_TYPE' => 'application/json'],
          json_encode([
            '_username' => $username,
            '_password' => $password,
          ])
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return $client;
    }

    /**
     * test getPagesAction
     */
    public function testGetPages()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/pages');
        // ...
    }

Or manually generate a JWT token for end-to-end testing:

.. code-block:: php

    use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

    protected static function createAuthenticatedClient(array $claims)
    {
        $client = self::createClient();
        $encoder = $client->getContainer()->get(JWTEncoderInterface::class);

        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $encoder->encode($claims)));

        return $client;
    }
