LexikJWTAuthenticationBundle
============================

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lexik/LexikJWTAuthenticationBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lexik/LexikJWTAuthenticationBundle/?branch=2.x)

[![Latest Stable Version](https://poser.pugx.org/lexik/jwt-authentication-bundle/v/stable.svg)](https://packagist.org/packages/lexik/jwt-authentication-bundle)

This bundle provides JWT (Json Web Token) authentication for your Symfony API.

It is compatible (and tested) with PHP 7.1+ on Symfony 4.x, 5.x and 6.x.

Documentation
-------------

The bulk of the documentation is stored in the [`Resources/doc`](Resources/doc/index.rst) directory of this bundle:

* [Getting started](Resources/doc/index.rst#getting-started)
  * [Prerequisites](Resources/doc/index.rst#prerequisites)
  * [Installation](Resources/doc/index.rst#installation)
  * [Configuration](Resources/doc/index.rst#configuration)
  * [Usage](Resources/doc/index.rst#usage)
  * [Notes](Resources/doc/index.rst#notes)
* [Further documentation](Resources/doc/index.rst#further-documentation)
  * [Configuration reference](Resources/doc/1-configuration-reference.rst)
  * [Data customization and validation](Resources/doc/2-data-customization.rst)
  * [Functional testing](Resources/doc/3-functional-testing.rst)
  * [Working with CORS requests](Resources/doc/4-cors-requests.rst)
  * [JWT encoder service customization](Resources/doc/5-encoder-service.rst)
  * [Extending Authenticator](Resources/doc/6-extending-jwt-authenticator.rst)
  * [Creating JWT tokens programmatically](Resources/doc/7-manual-token-creation.rst)
  * [A database-less user provider](Resources/doc/8-jwt-user-provider.rst)
  * [Accessing the authenticated JWT token](Resources/doc/9-access-authenticated-jwt-token.rst)

Community Support
-----------------

Please consider [opening a question on StackOverflow](http://stackoverflow.com/questions/ask) using the [`lexikjwtauthbundle` tag](http://stackoverflow.com/questions/tagged/lexikjwtauthbundle),  it is the official support platform for this bundle.
  
Github Issues are dedicated to bug reports and feature requests.

Contributing
------------

See the [CONTRIBUTING](CONTRIBUTING.md) file.


Sponsoring
----------

Huge thanks to [Blackfire](https://blackfire.io) and [JetBrains](https://jetbrains.com) for providing this project with free open-source licenses.

[![Blackfire](https://user-images.githubusercontent.com/7502063/178457752-520de30a-a2bc-4529-983b-6a3ff4f76045.png)](https://blackfire.io)

If you or your company use this package, please consider [sponsoring its maintenance and development](https://github.com/sponsors/chalasr).

Upgrading from 1.x
-------------------

Please see the [UPGRADE](UPGRADE-2.0.rst) file.

Credits
-------

* [Robin Chalas](https://github.com/chalasr)
* Lexik <dev@lexik.fr>
* [All contributors](https://github.com/lexik/LexikJWTAuthenticationBundle/graphs/contributors)

License
-------

This bundle is under the MIT license.  
For the whole copyright, see the [LICENSE](LICENSE) file distributed with this source code.
