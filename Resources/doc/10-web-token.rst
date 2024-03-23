Using Web-Token Feature
=======================

For v2.19, this bundle supports the Web-Token Framework to ease the
use of encrypted tokens and key rotations.

.. note::

    This feature is only available with PHP 8.1+ and Symfony 6.1+.


# Dependency Installation:

To enable this feature, you must install the following dependencies:

.. code-block:: sh

    composer require web-token/jwt-bundle

## About Algorithms

There are two type of signature algorithms: symmetric and asymmetric.

Symmetric algorithms are known to be very fast. They are mainly used when the issuer and
the recipient trust each other. The reason is that the key is symmetric i.e.
it is used both for signing and validating tokens.

Asymmetric algorithms are slower, but because keys are asymmetric you can allow recipients
for validating the tokens without allowing them to forge new ones. This is commonly used when
tokens are meant to be processed by third parties.

# Migrating Configuration

## Automatic Migration

A migration command is available when working with the debug mode enabled (dev environment).

.. code-block:: sh

    bin/console lexik:jwt:migrate-config

When running this command, the current configuration will be updated.
You should have no problem between the new and the previous one.
Please run all your tests to verify that everything went well after this migration.

After running the migration tool, your signature keyset will contain the key is exactly the same as the one you use,
but formatted as a JWK (Json Wek Key). In addition, two random keys are added. These keys are not used and will be
dropped when rotating the keyset.

## Manual Migration

If you want to migrate your configuration manually, you can follow the next steps.

### Signature Key and Keyset

The signature private key and the signature public keyset are mandatory for using the Web-Token library.
You can generate them using the following commands. Please use the one that corresponds to your algorithm.

.. code-block:: sh

    # We first need to convert the private key to a JWK.
    # If the private key is encrypted, you must provide the passphrase.
    # We take the opportunity to generate a random ID for the key and set the algorithm and the usage.
    # This is not mandatory, but it is a good practice.
    ./jose.phar key:load:key --random_id --use=sig --alg=RS256 --secret="testing" config/jwt/private.pem > config/jwt/signature.jwk

    # Next, we generate a random public keyset containing 3 private key.
    # Please use the same algorithm as the one used for the private key.
    ./jose.phar keyset:generate:rsa --random_id --use=sig --alg=RS256 --size 3 4096 > config/jwt/signature.jwkset
    ./jose.phar keyset:generate:oct --random_id --use=sig --alg=HS256 3 256 > config/jwt/signature.jwkset
    ./jose.phar keyset:generate:ec --random_id --use=sig --alg=ES256 3 P-256 > config/jwt/signature.jwkset
    ./jose.phar keyset:generate:okp --random_id --use=sig --alg=ED256 3 Ed25519 > config/jwt/signature.jwkset

    # Then, we rotate the keyset to add signautre private key to the keyset.
    ./jose.phar keyset:rotate `cat config/jwt/signature.jwkset` `cat config/jwt/signature.jwk` > config/jwt/signature.jwkset

### Bundle Configuration

The bundle configuration is very similar to the one used by the previous version of the bundle.
You just have to replace the ``lexik_jwt_authentication.encoder.***`` encoder by the ``lexik_jwt_authentication.encoder.web_token`` encoder.
Then, you have to set the access token issuance and verification parameters.

.. code-block:: yaml

    lexik_jwt_authentication:
        encoder:
            service: lexik_jwt_authentication.encoder.web_token # We use the Web-Token encoder
        access_token_issuance:
            enabled: true
            signature:
                algorithm: 'RS256'
                key: 'env(file:SIGNATURE_KEY)'
        access_token_verification:
            enabled: true
            signature:
                allowed_algorithms: ['RS256']
                keyset: 'env(file:SIGNATURE_KEYSET)'


In the example, we use the environment variables to retrieve the signature key and keyset.

.. code-block:: yaml

    # config/services.yaml
    parameters:
        env(SIGNATURE_KEY): '%kernel.project_dir%/config/jwt/signature.jwk'
        env(SIGNATURE_KEYSET): '%kernel.project_dir%/config/jwt/signature.jwkset'

.. note::

    We recommend using the environment variables to store the signature key and keyset instead of files.


# Encryption Support

With WebTokenBundle, you can encrypt your tokens. The tokens will only be readable by the applications
that have the private key to decrypt them.
The encryption support is not recommended unless the access tokens contain sensitive information.

Like the signature, the encryption requires a private key (for encryption) and a public keyset (for decryption).

A helper command is available when working with the debug mode enabled (dev environment).
This command will ask what algorithms to use and create the corresponding key and keyset.
The output will be the updated configuration for the bundle.

.. code-block:: sh

    bin/console lexik:jwt:enable-encryption


# Key Rotation

Among all the features offered by the Web-Token library,
you certainly want to rotate your keys on a regular basis.
Key rotation is a good practice preventing attackers guessing your keys and forging
tokens with elevated rights.

To ease the key manipulations, you should consider installing [JWT App](https://github.com/web-token/jwt-app).
You just have to download the last stable release from [the releases page](https://github.com/web-token/jwt-app/releases)
and set the PHAR file as executable.

.. code-block:: sh

    chmod +x jose.phar

In the following example, we will consider:
* The signature private key is stored in the ``config/jwt/signature.jwk`` file,
* The signature public keyset is stored in the ``config/jwt/signature.jwkset`` file.

The objective is to rotate the keyset by adding a new key and removing the oldest one.
The new private key will be stored in the ``config/jwt/signature.jwk`` file,
and the new public keyset will be updated.

## Signature Private Key

The new signature private key shall be compatible with the algorithm declared in the configuration.
For example, if you use the ``RS256`` algorithm, you must generate a RSA private key.
Hereafter few examples of RSA (``RS***``/``PS***``), OCT (``HS***``), EC (``ES***``) adn OKP (``ED***``) and private key generations:

.. code-block:: sh

    ./jose.phar key:generate:rsa --random_id --use=sig --alg=RS256 --size 4096 > config/jwt/signature.jwk
    ./jose.phar key:generate:oct --random_id --use=sig --alg=HS256 256 > config/jwt/signature.jwk
    ./jose.phar key:generate:ec --random_id --use=sig --alg=ES256 P-256 > config/jwt/signature.jwk
    ./jose.phar key:generate:okp --random_id --use=sig --alg=ED256 Ed25519 > config/jwt/signature.jwk

## Signature Public Keyset

Now that you have a new private key, you can rotate the public keyset.
The rotation is done by adding the new key at beginiing of the keyset and removing the oldest (last) one.

.. code-block:: sh

    ./jose.phar keyset:rotate `cat config/jwt/signature.jwkset` `cat config/jwt/signature.jwk` > config/jwt/signature.jwkset

## Encryption Key and Keyset

Encryption keys are managed in the same way as signature keys.
The  differences are as follows:
* You must use different files for the private and public keys (e.g. ``encryption.jwk`` and ``encryption.jwkset``),
* You must use the ``enc`` key usage,
* You must use the correct algorithm for the key type (RSA, OCT, EC, OKP).
