JWT Encoder configuration
=================================

This bundle comes with two [`namshi/jose`](https://github.com/namshi/jose) library based token encoders: `SharedKeyJWTEncoder` and `PublicPrivateKeyJWTEncoder`.

### SharedKeyJWTEncoder configuration
This encoder uses a string to encrypt and decrypt data. It allows you to use `HS256`, `HS384` and `HS512` signers from [`namshi/jose`](https://github.com/namshi/jose) library.

To use the `SharedKeyJWTEncoder` you have to:

1. Define the encoder as a service in your `config.yml`.

    ``` yaml
    services:
        lexik_jwt_encoder:
            class: Lexik\Bundle\JWTAuthenticationBundle\Encoder\SharedKeyJWTEncoder
            arguments: [ %shared_key_algorithm%, %shared_key% ]
    ```

1. Define the required parameters in your `parameters.yml`.

    ``` yaml
    parameters:
        shared_key_algorithm: HS512
        shared_key:           YourSharedKey
    ```


### PublicPrivateKeyJWTEncoder configuration
This encoder uses SSH keys to encrypt and decrypt data. It allows you to use `RS256`, `RS384` and `RS512` signers from [`namshi/jose`](https://github.com/namshi/jose) library.

To use the `PublicPrivateKeyJWTEncoder` you have to:

1. Generate the SSH keys :

    ``` bash
    $ openssl genrsa -out /path/to/keys/private.pem -aes256 4096
    $ openssl rsa -pubout -in /path/to/keys/private.pem -out app/var/jwt/public.pem
    ```

1. Define the encoder as a service in your `config.yml`.

    ``` yaml
    services:
        lexik_jwt_encoder:
            class: Lexik\Bundle\JWTAuthenticationBundle\Encoder\PublicPrivateKeyJWTEncoder
            arguments: [ %public_private_key_algorithm%, %private_key_path%, %public_key_path%, %pass_phrase% ]
    ```

1. Define the required parameters in your `parameters.yml`.

    ``` yaml
    parameters:
        public_private_key_algorithm: RS256
        public_key_path:              /path/to/keys/public.pem
        private_key_path:             /path/to/keys/private.pem
        pass_phrase:                  YourPassPhrase
    ```

### JWT encoder service customization

If this doesn't suit your needs, you can create your own encoder service. Here's an example implementing a [`nixilla/php-jwt`](https://github.com/nixilla/php-jwt) library based encoder.

1. Create the encoder class.

    ``` php
    // src/Acme/Bundle/ApiBundle/Encoder/NixillaJWTEncoder.php

    namespace Acme\Bundle\ApiBundle\Encoder;

    use JWT\Authentication\JWT;
    use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

    /**
     * NixillaJWTEncoder
     *
     * @author Nicolas Cabot <n.cabot@lexik.fr>
     */
    class NixillaJWTEncoder implements JWTEncoderInterface
    {
        /**
         * @var string
         */
        protected $key;

        /**
         * __construct
         */
        public function __construct($key = 'super_secret_key')
        {
            $this->key = $key;
        }

        /**
         * {@inheritdoc}
         */
        public function encode(array $data)
        {
            return JWT::encode($data, $this->key);
        }

        /**
         * {@inheritdoc}
         */
        public function decode($token)
        {
            try {
                return (array) JWT::decode($token, $this->key);
            } catch (\Exception $e) {
                return false;
            }
        }
    }
    ```

1. Declare it as a service.

    ``` yaml
    # Acme/Bundle/ApiBundle/Resources/config/services.yml

    services:
        acme_api.encoder.nixilla_jwt_encoder:
            class: Acme\Bundle\ApiBundle\Encoder\NixillaJWTEncoder
    ```

1. Set it as the encoder_service in the bundle configuration.

    ``` yaml
    # app/config/config.yml

    lexik_jwt_authentication:
        # ...
        encoder_service: acme_api.encoder.nixilla_jwt_encoder
    ```
