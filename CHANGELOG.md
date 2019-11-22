CHANGELOG
=========

For a diff between two versions https://github.com/lexik/LexikJWTAuthenticationBundle/compare/v1.0.0...v2.6.5

## [2.6.5](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.6.5) (2019-11-22)

* bug [\#689](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/689) Symfony 4.4/5.0 compatibility ([Deuchnord](https://github.com/Deuchnord))
* bug [\#687](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/687) Authentication Exception Message from its key ([arslan](https://github.com/arslan))
* bug [\#675](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/675) Use late static binding on JWTUser ([kaznovac](https://github.com/kaznovac))

## [2.6.4](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.6.4) (2019-07-27)

* bug [\#669](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/669) Fix dispatch signature on SF > 4.3 ([Webonaute](https://github.com/Webonaute))
* bug [\#650](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/650) Fixed AuthenticaionFailureHandler to utilize messages from custom exceptions ([EresDev](https://github.com/EresDev))

## [2.6.3](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.6.3) (2018-04-17)

* bug [\#644](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/644) Fix FC/BC layer for EventDispatcher ([nicolas-grekas](https://github.com/nicolas-grekas))

## [2.6.2](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.6.2) (2018-04-1)

* bug [\#637](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/637) Fix deprecations on symfony/event-dispatcher:4.3 ([chalasr](https://github.com/chalasr))
* bug [\#620](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/620) Fix missing $config variable ([Oliboy50](https://github.com/Oliboy50))
* bug [\#618](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/618) Use the JWTTokenManagerInterface ([trsteel88](https://github.com/trsteel88))
* bug [\#593](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/593) Make JWTManager::$userIdClaim nullable ([chalasr](https://github.com/chalasr))

## [2.6.1](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.6.1) (2018-11-18)

* bug [\#577](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/577) Fix argument order in JWTProvider service declaration ([fjogeleit](https://github.com/fjogeleit))

## [2.6.0](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.6.0) (2018-11-1)

* bug [\#574](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/574) fix clockSkew not taken into account in some case ([mu4ddi3](https://github.com/mu4ddi3))
* bug [\#554](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/554) Fix deprecations on Symfony 4.2 ([chalasr](https://github.com/chalasr))
* feature [\#537](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/537) Customizable User ID Claim  ([Spomky](https://github.com/Spomky))
* feature [\#503](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/503) Allow setting the "exp" claim from event listeners ([MaximeMaillet](https://github.com/MaximeMaillet))

## [2.5.4](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.5.4) (2018-08-2)

* bug [\#542](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/542) Fix missing implemenets breaking JWT header alteration ([tucksaun](https://github.com/tucksaun))

## [2.5.3](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.5.3) (2018-07-6)

* bug [\#525](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/525) Make openssl key loader service deprecated ([Faecie](https://github.com/Faecie))

## [2.5.2](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.5.2) (2018-07-3)

* bug [\#522](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/522) Fix clock skew + deprecation message ([chalasr](https://github.com/chalasr))

## [2.5.1](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.5.1) (2018-06-30)

* bug [\#515](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/515) Re-add namshi/jose as an hard requirement until 3.0 ([chalasr](https://github.com/chalasr))

## [2.5.0](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.5.0) (2018-06-29)

* feature [\#508](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/508) Replace namshi/jose by lcobucci/jwt ([chalasr](https://github.com/chalasr))
* feature [\#485](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/485) Add a `lexik:jwt:generate-token` command ([sroze](https://github.com/sroze))
* feature [\#369](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/369) Fix HMAC support ([chalasr](https://github.com/chalasr))
* feature [\#492](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/492) Clock skew ([patrickjDE](https://github.com/patrickjDE))
* feature [\#433](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/433) Added setPayload to JWTDecodedEvent analogous to JWTCreatedEvent. ([vgeyer](https://github.com/vgeyer))
* feature [\#412](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/412) Make the token type case insensitive ([greg0ire](https://github.com/greg0ire))
* feature [\#404](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/404) CheckConfigCommand should not be container aware ([chalasr](https://github.com/chalasr))
* feature [\#352](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/352) JWT header alteration ([Spomky](https://github.com/Spomky))
* feature [\#344](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/344) Add an extension point on the PayloadAwareUserProviderInterface ([sroze](https://github.com/sroze))

## [2.4.3](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.4.3) (2017-11-6)

* bug [\#408](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/408) Response classes shouldn't have the @internal PhpDoc tag ([lashae](https://github.com/lashae))
* bug [\#403](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/403) Switch to PSR-4 namespaces for PHPUnit ([chalasr](https://github.com/chalasr))
* bug [\#399](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/399) Fix sf3.4 command autoregistration deprecation ([ogizanagi](https://github.com/ogizanagi))

## [2.4.2](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.4.2) (2017-10-19)

* bug [\#398](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/398) Fix Symfony 4 compatibility ([benji07](https://github.com/benji07))
* bug [\#383](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/383) Don't register lcobucci encoder if lcobucci/jwt is not installed ([chalasr](https://github.com/chalasr))

## [2.4.1](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.4.1) (2017-08-29)

* bug [\#356](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/356) Dont use DefinitionDecorator on Symfony 3.3+ ([chalasr](https://github.com/chalasr))

## [2.4.0](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.4.0) (2017-05-10)

* feature [\#330](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/330) Allow empty ttl for testing purpose ([chalasr](https://github.com/chalasr))
* bug [\#328](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/328) Fix autowiring for upcoming Symfony 3.3 ([chalasr](https://github.com/chalasr))

## [2.3.0](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.3.0) (2017-04-14)

* bug [\#325](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/325) Move ttl `is_numeric` check from build time to runtime to allow use of %env()% ([DrBenton](https://github.com/DrBenton))
* feature [\#320](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/320) Allow for Response Body without JWT Token ([Batch1211](https://github.com/Batch1211))
* feature [\#317](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/317) Use symfony/phpunit-bridge for testing ([chalasr](https://github.com/chalasr))

## [2.2.0](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.2.0) (2017-03-09)

* feature [\#312](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/312) Ease sharing keys between parties ([chalasr](https://github.com/chalasr))
* bug [\#311](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/311) Handle empty or null authorization header prefix ([chteuchteu](https://github.com/chteuchteu))
* feature [\#303](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/303) Throw less missleading exception if SSL keys could not be loaded ([phansys](https://github.com/phansys))

## [2.1.1](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.1.1) (2017-01-23)

* bug [\#302](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/302) Return user object from User Provider refresh ([MisterGlass](https://github.com/MisterGlass))

## [2.1.0](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.1.0) (2016-12-30)

* feature [\#278](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/278) Add JWTUserProvider for loading users from the JWT itself ([chalasr](https://github.com/chalasr))
* bug [\#287](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/287) Avoid override existing properties in failure response ([kevin-lot](https://github.com/kevin-lot))

## [2.0.3](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.0.3) (2016-12-05)
* bug [\#285](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/285) Avoid validating key paths before container compilation ([chalasr](https://github.com/chalasr))
* feature [\#283](https//github.com/lexik/LexikJWTAuthenticationBundle/pull/283) Ease creating tokens programatically ([chalasr](https://github.com/chalasr))
* bug [\#282](https//github.com/lexik/LexikJWTAuthenticationBundle/pull/282) Catch exception from lcobucci parser on invalid but correctly formatted token ([chalasr](https://github.com/chalasr))
* feature [\#276](https//github.com/lexik/LexikJWTAuthenticationBundle/pull/276) Added `getProviderKey()` to JWTUserToken ([eXtreme](https://github.com/eXtreme))
* [\#280](https//github.com/lexik/LexikJWTAuthenticationBundle/pull/280) Travis: build on sf 3.2 + highest/lowest deps, fix build on hhvm ([chalasr](https://github.com/chalasr))
* [\#269](https//github.com/lexik/LexikJWTAuthenticationBundle/pull/269) Improve the structure of the documentation ([chalasr](https://github.com/chalasr))

## [2.0.2](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.0.2) (2016-10-27)

* feature [\#262](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/262) Add composer test script ([chalasr](https://github.com/chalasr))
* bug [\#261](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/261) The security token must be authenticated no matter of the user's roles ([chalasr](https://github.com/chalasr))

## [2.0.1](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.0.1) (2016-10-20)

* feature [\#257](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/257) Set autowiring types on services with many alternatives 

## [2.0.0](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v2.0.0) (2016-10-16)

* feature [\#249](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/249) Avoid setting exp claim from JWTManager ([chalasr](https://github.com/chalasr))
* feature [\#246](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/246) Add a simple built-in encoder based on lcobucci/jwt ([chalasr](https://github.com/chalasr))
* feature [\#240](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/240) Add iat check ([chalasr](https://github.com/chalasr))
* feature [\#230](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/230) Introduce JWTExpiredEvent ([chalasr](https://github.com/chalasr))
* feature [\#184](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/184) [Security] Deprecate current system in favor of a JWTTokenAuthenticator (Guard) ([chalasr](https://github.com/chalasr))
* feature [\#218](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/218) Add more flexibility in token extractors configuration ([chalasr](https://github.com/chalasr))
* feature [\#217](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/217) Refactor TokenExtractors loadi ng for easy overriding ([chalasr](https://github.com/chalasr))
* feature [\#202](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/202) Exceptions simplified ([Spomky](https://github.com/Spomky))
* feature [\#201](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/201) Remove deprecated request injections ([chalasr](https://github.com/chalasr))
* feature [\#196](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/196) Make *_key_path config options not mandatory ([chalasr](https://github.com/chalasr))
* feature [\#177](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/177) Add JWTAuthenticationResponse ([chalasr](https://github.com/chalasr))
* feature [\#162](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/162) [Encoder] Handle OpenSSL/phpseclib engines and algorithms ([chalasr](https://github.com/chalasr))

* [\#175](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/175) Stop ensuring support for PHP versions smaller than 5.0 ([chalasr](https://github.com/chalasr))

* [\#167](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/167) and [\#169](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/169) Stop ensuring support Symfony versions smaller than 2.8 ([chalasr](https://github.com/chalasr))

## [1.7.0](https://github.com/lexik/LexikJWTAuthenticationBundle/tree/v1.7.0) (2016-08-06)

* feature [\#200](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/200) Deprecate injection of Request instances ([chalasr](https://github.com/chalasr))

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
