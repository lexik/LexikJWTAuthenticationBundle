CHANGELOG
=========

For a diff between two versions https://github.com/lexik/LexikJWTAuthenticationBundle/compare/v1.0.0...v1.6.0

## [1.x](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/master)

* feature [\#200](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/200) Depreciate injection of Request instances ([chalasr](https://github.com/chalasr))

## [v1.6.0](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v1.6.0) (2016-07-07)

* feature [\#188](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/188) Add JWTNotFoundEvent ([chalasr](https://github.com/chalasr))

## [v1.5.1](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v1.5.1) (2016-04-11)

* bug [\#159](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/159) Fix anonymous access by removing the AuthenticationCredentialsNotFoundException  ([chalasr](https://github.com/chalasr))

## [v1.5.0](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v1.5.0) (2016-04-07)

* feature [\#157](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/157) Allow to set a custom response in case of authentication failure or invalid/not found token ([chalasr](https://github.com/chalasr))
* feature [\#154](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/154) Add OpenSSLKeyLoader ([chalasr](https://github.com/chalasr))
* feature [\#147](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/147) Made the public and private key paths not required… ([ovidiumght](https://github.com/ovidiumght))
* bug [\#142](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/142) Add response message in case of invalid token ([chalasr](https://github.com/chalasr))

## [v1.4.3](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v1.4.3) (2016-01-30)

* feature [\#133](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/133) Always call for master request from request stack ([stloyd](https://github.com/stloyd))

## [v1.4.1](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v1.4.1) (2016-01-21)

* feature [\#126](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/126) Use requestStack instead of request ([SmurfyFR](https://github.com/SmurfyFR))

## [v1.4.0](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v1.4.0) (2016-01-20)

* feature [\#117](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/117) Allow empty ttl ([soyuka](https://github.com/soyuka))
* feature [\#113](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/113) Add symfony 3.0 support ([Ener-Getick](https://github.com/Ener-Getick))
* feature [\#110](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/110) Updated to newest namshi/jose. Dropped support for PHP 5.3 ([TiS](https://github.com/TiS))
* feature [\#103](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/103) added functional boot test ([slashfan](https://github.com/slashfan))
* feature [\#96](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/96) Add custom authorization header name ([pdoreau](https://github.com/pdoreau))

## [v1.3.1](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v1.3.1) (2015-10-21)

* bug [\#101](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/101) Fatal error on console cache:clear ([ngandemer](https://github.com/ngandemer))

## [v1.3.0](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v1.3.0) (2015-10-21)

* feature [\#100](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/100) Add authentication_listener option ([yelmontaser](https://github.com/yelmontaser))

## [v1.2.0](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v1.2.0) (2015-09-28)

* bug [\#92](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/92) Fix authentication event propagation ([mRoca](https://github.com/mRoca))
* feature [\#88](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/88) Add WWW-Authenticate response header on 401 ([teohhanhui](https://github.com/teohhanhui))
* feature [\#76](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/76) Add cookie token extractor ([tnucera](https://github.com/tnucera))

## [v1.1.0](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v1.1.0) (2015-07-08)

* feature [\#73](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/73) add JWTEncodedEvent so JWT string is available after its creation ([9orky](https://github.com/9orky))
* feature [\#69](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/69) Added new event when token is authenticated ([gamringer](https://github.com/gamringer))

## [v1.0.10](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v1.0.10) (2015-06-05)

* feature [\#71](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/71) Fixing a missing use statement for Reference ([adetwiler](https://github.com/adetwiler))

## [v1.0.9](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v1.0.9) (2015-06-05)

* bug [\#70](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/70) fixed deprecated errors for symfony 2.6 plus ([slashfan](https://github.com/slashfan))
* feature [\#67](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/67) Move security details to parameters.yml.dist ([Maltronic](https://github.com/Maltronic))

## [v1.0.8](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v1.0.8) (2015-04-20)

* feature [\#63](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/63) Improve JWTProvider ([JJK801](https://github.com/JJK801))

## [v1.0.6](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v1.0.6) (2015-02-17)

* feature [\#45](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/45) Adding AuthenticationException to the AuthenticationFailureEvent ([ghost](https://github.com/ghost))
* feature [\#43](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/43) Added identity field funcionality and its unit test. ([victuxbb](https://github.com/victuxbb))
* feature [\#40](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/40) Add flexibilty to the provider and manager ([slashfan](https://github.com/slashfan))

## [v1.0.5](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v1.0.5) (2014-09-16)

* feature [\#28](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/28) Improve response and dispatch event in AuthenticationFailureHandler ([EmmanuelVella](https://github.com/EmmanuelVella))

## [v1.0.4](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v1.0.4) (2014-08-13)

* feature [\#27](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/27) Added encoder / decoder service customization \(\#24\) ([slashfan](https://github.com/slashfan))
* feature [\#19](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/19) Add response in success event ([EmmanuelVella](https://github.com/EmmanuelVella))
* feature [\#18](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/18) Improve json 401 exception ([EmmanuelVella](https://github.com/EmmanuelVella))

## [v1.0.2](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v1.0.2) (2014-07-11)

* feature [\#15](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/15) Added JWT Creator service ([gfreeau](https://github.com/gfreeau))

## [v1.0.0](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v1.0.0) (2014-05-16)

* feature [\#10](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/10) Added ability to throw exceptions for handling later and to disable the catch-all entry point ([gfreeau](https://github.com/gfreeau))
* feature [\#9](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/9) Changed entry point to contain a message and return json ([gfreeau](https://github.com/gfreeau))
* bug [\#7](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/7) Jwt entry point fix \#6 ([jaugustin](https://github.com/jaugustin))
* feature [\#5](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/5) Firewall config ([slashfan](https://github.com/slashfan))
* feature [\#2](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/2) Symfony2.3+ compatibility ([slashfan](https://github.com/slashfan))
