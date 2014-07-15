LexikJWTAuthenticationBundle
============================

[![Build Status](https://travis-ci.org/lexik/LexikJWTAuthenticationBundle.svg?branch=master)](https://travis-ci.org/lexik/LexikJWTAuthenticationBundle)
[![Latest Stable Version](https://poser.pugx.org/lexik/jwt-authentication-bundle/v/stable.svg)](https://packagist.org/packages/lexik/jwt-authentication-bundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/67573b6f-e182-4394-b26a-649c323784f6/mini.png)](https://insight.sensiolabs.com/projects/67573b6f-e182-4394-b26a-649c323784f6)

This bundle provides JWT (Json Web Token) authentication for your Symfony2 REST API using the [`namshi/jose`](https://github.com/namshi/jose) library.

It has been tested using PHP5.3 to PHP5.6 and HHVM, and Symfony2.3 to Symfony2.5.

Documentation
-------------

The bulk of the documentation is stored in the `Resources/doc/index.md` file in this bundle:

[Read the documentation](Resources/doc/index.md)

Testing
-------

Setup the test suite using [Composer](http://getcomposer.org/):

    $ composer install --dev

Run it using PHPUnit:

    $ bin/phpunit

Contributing
------------

See [CONTRIBUTING](CONTRIBUTING.md) file.


Credits
-------

* Lexik <dev@lexik.fr>
* [All contributors](https://github.com/lexik/LexikJWTAuthenticationBundle/graphs/contributors)

License
-------

This bundle is under the MIT license. See the complete license in the bundle:

    Resources/meta/LICENSE
