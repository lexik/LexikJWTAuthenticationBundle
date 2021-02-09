<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\Guard;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidPayloadException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\MissingTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\UserNotFoundException;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Tests\Stubs\User as AdvancedUserStub;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class JWTTokenAuthenticatorTest extends TestCase
{
    public function testGetCredentials()
    {
        $jwtManager = $this->getJWTManagerMock();
        $jwtManager
            ->expects($this->once())
            ->method('decode')
            ->willReturn(['username' => 'lexik']);

        $authenticator = new JWTTokenAuthenticator(
            $jwtManager,
            $this->getEventDispatcherMock(),
            $this->getTokenExtractorMock('token'),
            $this->getTokenStorageMock()
        );

        $this->assertInstanceOf(PreAuthenticationJWTUserToken::class, $authenticator->getCredentials($this->getRequestMock()));
    }

    public function testGetCredentialsWithInvalidTokenThrowsException()
    {
        try {
            (new JWTTokenAuthenticator(
                $this->getJWTManagerMock(),
                $this->getEventDispatcherMock(),
                $this->getTokenExtractorMock('token'),
                $this->getTokenStorageMock()
            ))->getCredentials($this->getRequestMock());

            $this->fail(sprintf('Expected exception of type "%s" to be thrown.', InvalidTokenException::class));
        } catch (InvalidTokenException $e) {
            $this->assertSame('Invalid JWT Token', $e->getMessageKey());
        }
    }

    public function testGetCredentialsWithExpiredTokenThrowsException()
    {
        $jwtManager = $this->getJWTManagerMock();
        $jwtManager
            ->expects($this->once())
            ->method('decode')
            ->with(new PreAuthenticationJWTUserToken('token'))
            ->will($this->throwException(new JWTDecodeFailureException(JWTDecodeFailureException::EXPIRED_TOKEN, 'Expired JWT Token')));

        try {
            (new JWTTokenAuthenticator(
                $jwtManager,
                $this->getEventDispatcherMock(),
                $this->getTokenExtractorMock('token'),
                $this->getTokenStorageMock()
            ))->getCredentials($this->getRequestMock());

            $this->fail(sprintf('Expected exception of type "%s" to be thrown.', ExpiredTokenException::class));
        } catch (ExpiredTokenException $e) {
            $this->assertSame('Expired JWT Token', $e->getMessageKey());
        }
    }

    public function testGetCredentialsReturnsNullWithoutToken()
    {
        $authenticator = new JWTTokenAuthenticator(
            $this->getJWTManagerMock(),
            $this->getEventDispatcherMock(),
            $this->getTokenExtractorMock(false),
            $this->getTokenStorageMock()
        );

        $this->assertNull($authenticator->getCredentials($this->getRequestMock()));
    }

    public function testGetUser()
    {
        $userIdClaim = 'sub';
        $payload     = [$userIdClaim => 'lexik'];
        $rawToken    = 'token';
        $userRoles   = ['ROLE_USER'];

        $userStub = new AdvancedUserStub('lexik', 'password', 'user@gmail.com', $userRoles);

        $decodedToken = new PreAuthenticationJWTUserToken($rawToken);
        $decodedToken->setPayload($payload);

        $userProvider = $this->getUserProviderMock();
        $userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with($payload[$userIdClaim])
            ->willReturn($userStub);

        $authenticator = new JWTTokenAuthenticator(
            $this->getJWTManagerMock(null, $userIdClaim),
            $this->getEventDispatcherMock(),
            $this->getTokenExtractorMock(),
            $this->getTokenStorageMock()
        );

        $this->assertSame($userStub, $authenticator->getUser($decodedToken, $userProvider));
    }

    public function testGetUserWithInvalidPayloadThrowsException()
    {
        $decodedToken = new PreAuthenticationJWTUserToken('rawToken');
        $decodedToken->setPayload([]); // Empty payload

        try {
            (new JWTTokenAuthenticator(
                $this->getJWTManagerMock(null, 'username'),
                $this->getEventDispatcherMock(),
                $this->getTokenExtractorMock(),
                $this->getTokenStorageMock()
            ))->getUser($decodedToken, $this->getUserProviderMock());

            $this->fail(sprintf('Expected exception of type "%s" to be thrown.', InvalidPayloadException::class));
        } catch (InvalidPayloadException $e) {
            $this->assertSame('Unable to find key "username" in the token payload.', $e->getMessageKey());
        }
    }

    public function testGetUserWithInvalidFirstArg()
    {
        $this->expectException(\InvalidArgumentException::class);

        (new JWTTokenAuthenticator(
            $this->getJWTManagerMock(),
            $this->getEventDispatcherMock(),
            $this->getTokenExtractorMock(),
            $this->getTokenStorageMock()
        ))->getUser(new \stdClass(), $this->getUserProviderMock());
    }

    public function testGetUserWithInvalidUserThrowsException()
    {
        $userIdClaim = 'username';
        $payload     = [$userIdClaim => 'lexik'];

        $decodedToken = new PreAuthenticationJWTUserToken('rawToken');
        $decodedToken->setPayload($payload);

        $userProvider = $this->getUserProviderMock();
        $userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with($payload[$userIdClaim])
            ->will($this->throwException(new UsernameNotFoundException()));

        try {
            (new JWTTokenAuthenticator(
                $this->getJWTManagerMock(null, 'username'),
                $this->getEventDispatcherMock(),
                $this->getTokenExtractorMock(),
                $this->getTokenStorageMock()
            ))->getUser($decodedToken, $userProvider);

            $this->fail(sprintf('Expected exception of type "%s" to be thrown.', UserNotFoundException::class));
        } catch (UserNotFoundException $e) {
            $this->assertSame('Unable to load an user with property "username" = "lexik". If the user identity has changed, you must renew the token. Otherwise, verify that the "lexik_jwt_authentication.user_identity_field" config option is correctly set.', $e->getMessageKey());
        }
    }

    public function testCreateAuthenticatedToken()
    {
        $rawToken  = 'token';
        $userRoles = ['ROLE_USER'];
        $payload   = ['sub' => 'lexik'];
        $userStub  = new AdvancedUserStub('lexik', 'password', 'user@gmail.com', $userRoles);

        $decodedToken = new PreAuthenticationJWTUserToken($rawToken);
        $decodedToken->setPayload($payload);

        $jwtUserToken = new JWTUserToken($userRoles, $userStub, $rawToken, 'lexik');

        $tokenStorage = $this->getTokenStorageMock();

        $tokenStorage->expects(self::exactly(2))->method('setToken')->withConsecutive([$decodedToken], [null]);
        $tokenStorage->expects(self::once())->method('getToken')->willReturn($decodedToken);

        $dispatcher = $this->getEventDispatcherMock();
        $this->expectEvent(Events::JWT_AUTHENTICATED, new JWTAuthenticatedEvent($payload, $jwtUserToken), $dispatcher);

        $authenticator = new JWTTokenAuthenticator(
            $this->getJWTManagerMock(null, 'sub'),
            $dispatcher,
            $this->getTokenExtractorMock(),
            $tokenStorage
        );

        $userProvider = $this->getUserProviderMock();
        $userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with($payload['sub'])
            ->willReturn($userStub);

        $authenticator->getUser($decodedToken, $userProvider);

        $this->assertEquals($jwtUserToken, $authenticator->createAuthenticatedToken($userStub, 'lexik'));
    }

    public function testCreateAuthenticatedTokenThrowsExceptionIfNotPreAuthenticatedToken()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to return an authenticated token');

        $userStub = new AdvancedUserStub('lexik', 'test');

        $tokenStorage = $this->getTokenStorageMock();

        $tokenStorage->expects(self::never())->method('setToken');
        $tokenStorage->expects(self::once())->method('getToken')->willReturn(null);

        (new JWTTokenAuthenticator(
           $this->getJWTManagerMock(),
           $this->getEventDispatcherMock(),
           $this->getTokenExtractorMock(),
           $tokenStorage
       ))->createAuthenticatedToken($userStub, 'lexik');
    }

    public function testOnAuthenticationFailureWithInvalidToken()
    {
        $authException    = new InvalidTokenException();
        $expectedResponse = new JWTAuthenticationFailureResponse('Invalid JWT Token');

        $dispatcher = $this->getEventDispatcherMock();
        $this->expectEvent(Events::JWT_INVALID, new JWTInvalidEvent($authException, $expectedResponse), $dispatcher);

        $authenticator = new JWTTokenAuthenticator(
            $this->getJWTManagerMock(),
            $dispatcher,
            $this->getTokenExtractorMock(),
            $this->getTokenStorageMock()
        );

        $response = $authenticator->onAuthenticationFailure($this->getRequestMock(), $authException);

        $this->assertEquals($expectedResponse, $response);
        $this->assertSame($expectedResponse->getMessage(), $response->getMessage());
    }

    public function testStart()
    {
        $authException   = new MissingTokenException('JWT Token not found');
        $failureResponse = new JWTAuthenticationFailureResponse($authException->getMessageKey());

        $dispatcher = $this->getEventDispatcherMock();
        $this->expectEvent(Events::JWT_NOT_FOUND, new JWTNotFoundEvent($authException, $failureResponse), $dispatcher);

        $authenticator = new JWTTokenAuthenticator(
            $this->getJWTManagerMock(),
            $dispatcher,
            $this->getTokenExtractorMock(),
            $this->getTokenStorageMock()
        );

        $response = $authenticator->start($this->getRequestMock());

        $this->assertEquals($failureResponse, $response);
        $this->assertSame($failureResponse->getMessage(), $response->getMessage());
    }

    public function testCheckCredentials()
    {
        $user = new AdvancedUserStub('test', 'test');

        $this->assertTrue(
            (new JWTTokenAuthenticator(
                $this->getJWTManagerMock(),
                $this->getEventDispatcherMock(),
                $this->getTokenExtractorMock(),
                $this->getTokenStorageMock()
            ))->checkCredentials(null, $user)
        );
    }

    public function testSupportsRememberMe()
    {
        $this->assertFalse(
            (new JWTTokenAuthenticator(
                $this->getJWTManagerMock(),
                $this->getEventDispatcherMock(),
                $this->getTokenExtractorMock(),
                $this->getTokenStorageMock()
            ))->supportsRememberMe()
        );
    }

    private function getJWTManagerMock($userIdentityField = null, $userIdClaim = null)
    {
        $jwtManager = $this->getMockBuilder(JWTTokenManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        if (null !== $userIdentityField) {
            $jwtManager
                ->expects($this->once())
                ->method('getUserIdentityField')
                ->willReturn($userIdentityField);
        }
        if (null !== $userIdClaim) {
            $jwtManager
                ->expects($this->once())
                ->method('getUserIdClaim')
                ->willReturn($userIdClaim);
        }

        return $jwtManager;
    }

    private function getEventDispatcherMock()
    {
        return $this->getMockBuilder(EventDispatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getTokenExtractorMock($returnValue = null)
    {
        $extractor = $this->getMockBuilder(TokenExtractorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        if (null !== $returnValue) {
            $extractor
                ->expects($this->once())
                ->method('extract')
                ->willReturn($returnValue);
        }

        return $extractor;
    }

    private function getRequestMock()
    {
        return $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getUserProviderMock()
    {
        return $this->getMockBuilder(UserProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|TokenStorageInterface
     */
    private function getTokenStorageMock()
    {
        return $this->getMockBuilder(TokenStorageInterface::class)
            ->setMethods(['getToken', 'setToken'])
            ->getMockForAbstractClass();
    }

    private function expectEvent($eventName, $event, $dispatcher)
    {
        if ($dispatcher instanceof ContractsEventDispatcherInterface) {
            $dispatcher->expects($this->once())->method('dispatch')->with($event, $eventName);

            return;
        }

        $dispatcher->expects($this->once())->method('dispatch')->with($eventName, $event);
    }
}
