JWT encoder service customization
=================================

This bundle comes with two built-in token encoders, one based on the [`namshi/jose`](https://github.com/namshi/jose) library (default) and the later based on the [`lcobucci/jwt`](https://github.com/lcobucci/jwt) library.
If both don't suit your needs, you can replace it with your own encoder service. Here's an example implementing a [`nixilla/php-jwt`](https://github.com/nixilla/php-jwt) library based encoder.

Creating your own encoder
--------------------------

### Create the encoder class

``` php
// src/App/Encoder/NixillaJWTEncoder.php
namespace App\Encoder;

use JWT\Authentication\JWT;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;

/**
 * NixillaJWTEncoder
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class NixillaJWTEncoder implements JWTEncoderInterface
{
    private $key;

    public function __construct(string $key = 'super_secret_key')
    {
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(array $data)
    {
        try {
            return JWT::encode($data, $this->key);
        }
        catch (\Exception $e) {
            throw new JWTEncodeFailureException(JWTEncodeFailureException::INVALID_CONFIG, 'An error occurred while trying to encode the JWT token.', $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function decode($token)
    {
        try {
            return (array) JWT::decode($token, $this->key);
        } catch (\Exception $e) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, 'Invalid JWT Token', $e);
        }
    }
}
```

### Declare it as a service

``` yaml
# config/services.yaml
services:
    acme_api.encoder.nixilla_jwt_encoder:
        class: App\Encoder\NixillaJWTEncoder
```

### Use it as encoder service

``` yaml
# config/packages/lexik_jwt_authentication.yaml
lexik_jwt_authentication:
    # ...
    encoder:
        service: acme_api.encoder.nixilla_jwt_encoder
```

__Note__  
You can use the `lexik_jwt_authentication.encoder.crypto_engine` and `lexik_jwt_authentication.encoder.signature_algorithm` parameters that represent the corresponding configuration options by injecting them as argument of the encoder's service, then use them through the library on which the encoder is based on.  
See the [configuration reference](1-configuration-reference.md) for more informations.
