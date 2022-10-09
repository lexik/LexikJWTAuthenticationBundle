<?php

declare(strict_types=1);

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\Authenticator;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidPayloadException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\MissingTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\Token\JWTPostAuthenticationToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\PayloadAwareUserProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Tests\Stubs\User as AdvancedUserStub;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/** @requires PHP >= 7.2 */
class JWTAuthenticatorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (!class_exists(UserNotFoundException::class)) {
            $this->markTestSkipped('This test suite only concerns the new Symfony 5.3 authentication system.');
        }
    }

    public function testAuthenticate()
    {
        $userIdClaim = 'sub';
        $payload = [$userIdClaim => 'lexik'];
        $rawToken = 'token';
        $userRoles = ['ROLE_USER'];

        $userStub = new AdvancedUserStub('lexik', 'password', 'user@gmail.com', $userRoles);

        $jwtManager = $this->getJWTManagerMock(null, $userIdClaim);
        $jwtManager
            ->method('parse')
            ->willReturn(['sub' => 'lexik']);

        $userProvider = $this->getUserProviderMock();
        $userProvider
            ->method('loadUserByIdentifierAndPayload')
            ->with($payload['sub'], $payload)
            ->willReturn($userStub);

        $authenticator = new JWTAuthenticator(
            $jwtManager,
            $this->getEventDispatcherMock(),
            $this->getTokenExtractorMock($rawToken),
            $userProvider
        );

        $this->assertSame($userStub, ($authenticator->authenticate($this->getRequestMock()))->getUser());
    }

    public function testAuthenticateWithIntegerIdentifier()
    {
        $userIdClaim = 'sub';
        $payload = [$userIdClaim => 1];
        $rawToken = 'token';
        $userRoles = ['ROLE_USER'];

        $userStub = new AdvancedUserStub(1, 'password', 'user@gmail.com', $userRoles);

        $jwtManager = $this->getJWTManagerMock(null, $userIdClaim);
        $jwtManager
            ->method('parse')
            ->willReturn(['sub' => 1]);

        $userProvider = $this->getUserProviderMock();
        $userProvider
            ->method('loadUserByIdentifierAndPayload')
            ->with($payload['sub'], $payload)
            ->willReturn($userStub);

        $authenticator = new JWTAuthenticator(
            $jwtManager,
            $this->getEventDispatcherMock(),
            $this->getTokenExtractorMock($rawToken),
            $userProvider
        );

        $this->assertSame($userStub, ($authenticator->authenticate($this->getRequestMock()))->getUser());
    }

    public function testAuthenticateWithExpiredTokenThrowsException()
    {
        $jwtManager = $this->getJWTManagerMock();
        $jwtManager->method('parse')
            ->will($this->throwException(new JWTDecodeFailureException(JWTDecodeFailureException::EXPIRED_TOKEN, 'Expired JWT Token')));

        $this->expectException(ExpiredTokenException::class);

        $authenticator = new JWTAuthenticator(
            $jwtManager,
            $this->getEventDispatcherMock(),
            $this->getTokenExtractorMock('token'),
            $this->getUserProviderMock()
        );

        $authenticator->authenticate($this->getRequestMock());
    }

    public function testAuthenticateWithInvalidTokenThrowsException()
    {
        $jwtManager = $this->getJWTManagerMock();
        $jwtManager->method('parse')
            ->willThrowException(
                new JWTDecodeFailureException(
                    JWTDecodeFailureException::INVALID_TOKEN,
                    'Invalid JWT Token'
                )
            );
        $authenticator = new JWTAuthenticator(
            $jwtManager,
            $this->getEventDispatcherMock(),
            $this->getTokenExtractorMock('token'),
            $this->getUserProviderMock()
        );

        $this->expectException(InvalidTokenException::class);

        $authenticator->authenticate($this->getRequestMock());
    }

    public function testAuthenticateWithUndecodableTokenThrowsException()
    {
        $jwtManager = $this->getJWTManagerMock();
        $jwtManager->method('parse')
            ->willThrowException(new JWTDecodeFailureException(
                JWTDecodeFailureException::INVALID_TOKEN,
                'The token was marked as invalid by an event listener after successful decoding.'
            ));

        $authenticator = new JWTAuthenticator(
            $jwtManager,
            $this->getEventDispatcherMock(),
            $this->getTokenExtractorMock('token'),
            $this->getUserProviderMock()
        );

        $this->expectException(InvalidTokenException::class);

        $authenticator->authenticate($this->getRequestMock());
    }

    public function testAuthenticationWithInvalidPayloadThrowsException()
    {
        $jwtManager = $this->getJWTManagerMock();
        $jwtManager->method('parse')
            ->willReturn(['foo' => 'bar']);
        $jwtManager->method('getUserIdClaim')
            ->willReturn('identifier');
        $authenticator = new JWTAuthenticator(
            $jwtManager,
            $this->getEventDispatcherMock(),
            $this->getTokenExtractorMock('token'),
            $this->getUserProviderMock()
        );

        $this->expectException(InvalidPayloadException::class);

        $authenticator->authenticate($this->getRequestMock());
    }

    public function testAuthenticateWithInvalidUserThrowsException()
    {
        $jwtManager = $this->getJWTManagerMock();
        $jwtManager->method('parse')
            ->willReturn(['identifier' => 'bar']);
        $jwtManager->method('getUserIdClaim')
            ->willReturn('identifier');

        $userProvider = $this->getUserProviderMock();
        $userProvider->method('loadUserByIdentifierAndPayload')
            ->willThrowException(new UserNotFoundException());

        $authenticator = new JWTAuthenticator(
            $jwtManager,
            $this->getEventDispatcherMock(),
            $this->getTokenExtractorMock('token'),
            $userProvider
        );

        $this->expectException(UserNotFoundException::class);

        $authenticator->authenticate($this->getRequestMock())->getUser();
    }

    public function testOnAuthenticationFailureWithInvalidToken()
    {
        $authException = new InvalidTokenException();
        $expectedResponse = new JWTAuthenticationFailureResponse('Invalid JWT Token');
        $request = $this->getRequestMock();
        $dispatcher = $this->getEventDispatcherMock();
        $this->expectEvent(Events::JWT_INVALID, new JWTInvalidEvent($authException, $expectedResponse, $request), $dispatcher);

        $authenticator = new JWTAuthenticator(
            $this->getJWTManagerMock(),
            $dispatcher,
            $this->getTokenExtractorMock(),
            $this->getUserProviderMock()
        );

        $response = $authenticator->onAuthenticationFailure($request, $authException);

        $this->assertEquals($expectedResponse, $response);
        $this->assertSame($expectedResponse->getMessage(), $response->getMessage());
    }

    public function testOnAuthenticationFailureWithInvalidTokenTranslatedMessage()
    {
        $authException = new InvalidTokenException();
        $expectedResponse = new JWTAuthenticationFailureResponse('translated message');
        $request = $this->getRequestMock();
        $dispatcher = $this->getEventDispatcherMock();
        $this->expectEvent(Events::JWT_INVALID, new JWTInvalidEvent($authException, $expectedResponse, $request), $dispatcher);

        $translator = $this->getTranslatorMock();
        $translator->expects($this->once())
            ->method('trans')
            ->with('Invalid JWT Token', [])
            ->willReturn('translated message');

        $authenticator = new JWTAuthenticator(
            $this->getJWTManagerMock(),
            $dispatcher,
            $this->getTokenExtractorMock(),
            $this->getUserProviderMock(),
            $translator
        );

        $response = $authenticator->onAuthenticationFailure($request, $authException);

        $this->assertEquals($expectedResponse, $response);
        $this->assertSame('translated message', $response->getMessage());
    }

    public function testStart()
    {
        $authException = new MissingTokenException('JWT Token not found');
        $failureResponse = new JWTAuthenticationFailureResponse($authException->getMessageKey());
        $request = $this->getRequestMock();
        $dispatcher = $this->getEventDispatcherMock();
        $this->expectEvent(Events::JWT_NOT_FOUND, new JWTNotFoundEvent($authException, $failureResponse, $request), $dispatcher);

        $authenticator = new JWTAuthenticator(
            $this->getJWTManagerMock(),
            $dispatcher,
            $this->getTokenExtractorMock(),
            $this->getUserProviderMock()
        );

        $response = $authenticator->start($request);

        $this->assertEquals($failureResponse, $response);
        $this->assertSame($failureResponse->getMessage(), $response->getMessage());
    }

    public function testCreateAuthenticatedToken()
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getRoles')->willReturn(['ROLE_USER']);

        $dispatcher = $this->getEventDispatcherMock();
        $dispatcher->expects($this->once())->method('dispatch')->with($this->equalTo(new JWTAuthenticatedEvent(['claim' => 'val'], new JWTPostAuthenticationToken($user, 'dummy', ['ROLE_USER'], 'dummytoken'))), Events::JWT_AUTHENTICATED);

        $authenticator = new JWTAuthenticator(
            $this->getJWTManagerMock(),
            $dispatcher,
            $this->getTokenExtractorMock(),
            $this->getUserProviderMock()
        );

        $passport = $this->createMock(Passport::class);
        $passport->method('getUser')->willReturn($user);
        $passport->method('getAttribute')
            ->withConsecutive(['token', null], ['payload', null])
            ->willReturnOnConsecutiveCalls('dummytoken', ['claim' => 'val']);

        if (method_exists(FormLoginAuthenticator::class, 'createToken')) {
            $token = $authenticator->createToken($passport, 'dummy');
        } else {
            $token = $authenticator->createAuthenticatedToken($passport, 'dummy');
        }

        $this->assertInstanceOf(JWTPostAuthenticationToken::class, $token);
        $this->assertSame('dummytoken', $token->getCredentials());
    }

    public function testParsingAnInvalidTokenThrowsException()
    {
        $jwtManager = $this->getJWTManagerMock();
        $jwtManager->method('parse')
            ->willThrowException(new InvalidTokenException('Unable to extract JWT token'));

        $authenticator = new JWTAuthenticator(
            $jwtManager,
            $this->getEventDispatcherMock(),
            $this->getTokenExtractorMock(false),
            $this->getUserProviderMock()
        );

        $this->expectException(\LogicException::class);

        $authenticator->authenticate($this->getRequestMock());
    }

    private function getJWTManagerMock($userIdentityField = null, $userIdClaim = null)
    {
        $jwtManager = $this->getMockBuilder(DummyJWTManager::class)
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
        return $this->getMockBuilder(DummyUserProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getTranslatorMock()
    {
        return $this->getMockBuilder(TranslatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function expectEvent($eventName, $event, $dispatcher)
    {
        $dispatcher->expects($this->once())->method('dispatch')->with($event, $eventName);
    }
}

abstract class DummyUserProvider implements UserProviderInterface, PayloadAwareUserProviderInterface
{
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
    }

    public function loadUserByIdentifierAndPayload(string $identifier): UserInterface
    {
    }
}

abstract class DummyJWTManager implements JWTTokenManagerInterface
{
    public function parse(string $token): array
    {
    }
}
