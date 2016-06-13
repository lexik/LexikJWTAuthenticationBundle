JWT encoder service customization
=================================

This bundle comes with a [`namshi/jose`](https://github.com/namshi/jose) library based token encoder as it uses SSH keys to encrypt and decrypt data.
If this doesn't suit your needs, you can replace it with your own encoder service. Here's an example implementing a [`nixilla/php-jwt`](https://github.com/nixilla/php-jwt) library based encoder.

### Create the encoder class

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

### Declare it as a service

``` yaml
# services.yml
services:
    acme_api.encoder.nixilla_jwt_encoder:
        class: Acme\Bundle\ApiBundle\Encoder\NixillaJWTEncoder
```

### Set it as the encoder_service in the bundle configuration

``` yaml
# config.yml
lexik_jwt_authentication:
    # ...
    encoder_service: acme_api.encoder.nixilla_jwt_encoder
```

__Note__  
You can use the `lexik_jwt_authentication.encoder.encryption_engine` and `lexik_jwt_authentication.encoder.encryption_algorithm` parameters that represent the corresponding configuration options by injecting them as argument of the encoder's service, then use them through the library on which the encoder is based on.  
See the [configuration reference](1-configuration-reference.md) for more informations.
