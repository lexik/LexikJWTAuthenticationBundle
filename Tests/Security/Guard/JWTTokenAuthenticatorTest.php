<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\Guard;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTAuthenticationException;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Tests\Stubs\User as AdvancedUserStub;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class JWTTokenAuthenticatorTest extends \PHPUnit_Framework_TestCase
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
            $this->getTokenExtractorMock('token')
        );

        $this->assertInstanceOf(PreAuthenticationJWTUserToken::class, $authenticator->getCredentials($this->getRequestMock()));
    }

    /**
     * @expectedException        \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTAuthenticationException
     * @expectedExceptionMessage Invalid JWT Token
     */
    public function testGetCredentialsWithInvalidToken()
    {
        (new JWTTokenAuthenticator(
            $this->getJWTManagerMock(),
            $this->getEventDispatcherMock(),
            $this->getTokenExtractorMock('token')
        ))->getCredentials($this->getRequestMock());
    }

    public function testGetCredentialsWithoutToken()
    {
        $authenticator = new JWTTokenAuthenticator(
            $this->getJWTManagerMock(),
            $this->getEventDispatcherMock(),
            $this->getTokenExtractorMock(false)
        );

        $this->assertNull($authenticator->getCredentials($this->getRequestMock()));
    }

    public function testGetUser()
    {
        $userIdentityField = 'username';
        $payload           = [$userIdentityField => 'lexik'];
        $rawToken          = 'token';
        $userRoles         = ['ROLE_USER'];

        $dispatcher = $this->getEventDispatcherMock();
        $userStub   = new AdvancedUserStub('lexik', 'password', 'user@gmail.com', $userRoles);

        $jwtUserToken = new JWTUserToken($userRoles);
        $jwtUserToken->setUser($userStub);
        $jwtUserToken->setRawToken($rawToken);

        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(Events::JWT_AUTHENTICATED, new JWTAuthenticatedEvent($payload, $jwtUserToken));

        $decodedToken = new PreAuthenticationJWTUserToken($rawToken);
        $decodedToken->setPayload($payload);

        $userProvider = $this->getUserProviderMock();
        $userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with($payload[$userIdentityField])
            ->willReturn($userStub);

        $authenticator = new JWTTokenAuthenticator(
            $this->getJWTManagerMock('username'),
            $dispatcher,
            $this->getTokenExtractorMock()
        );

        $this->assertSame($userStub, $authenticator->getUser($decodedToken, $userProvider));
    }

    /**
     * @expectedException        \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTAuthenticationException
     * @expectedExceptionMessage Unable to find a key corresponding to the configured user_identity_field ("username")
     */
    public function testGetUserWithInvalidPayload()
    {
        $decodedToken = new PreAuthenticationJWTUserToken('rawToken');
        $decodedToken->setPayload([]); // Empty payload

        (new JWTTokenAuthenticator(
            $this->getJWTManagerMock('username'),
            $this->getEventDispatcherMock(),
            $this->getTokenExtractorMock()
        ))->getUser($decodedToken, $this->getUserProviderMock());
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage must be an instance of "Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserToken".
     */
    public function testGetUserWithInvalidFirstArg()
    {
        (new JWTTokenAuthenticator(
            $this->getJWTManagerMock(),
            $this->getEventDispatcherMock(),
            $this->getTokenExtractorMock()
        ))->getUser(new \stdClass(), $this->getUserProviderMock());
    }

    /**
     * @expectedException        \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTAuthenticationException
     * @expectedExceptionMessage Unable to load a valid user with property "username" = "lexik"
     */
    public function testGetUserWithInvalidUser()
    {
        $userIdentityField = 'username';
        $payload           = [$userIdentityField => 'lexik'];

        $decodedToken = new PreAuthenticationJWTUserToken('rawToken');
        $decodedToken->setPayload($payload);

        $userProvider = $this->getUserProviderMock();
        $userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with($payload[$userIdentityField])
            ->will($this->throwException(new UsernameNotFoundException()));

        (new JWTTokenAuthenticator(
            $this->getJWTManagerMock('username'),
            $this->getEventDispatcherMock(),
            $this->getTokenExtractorMock()
        ))->getUser($decodedToken, $userProvider);
    }

    public function testOnAuthenticationFailureWithInvalidToken()
    {
        $authException    = new JWTAuthenticationException('Invalid JWT Token');
        $expectedResponse = new JWTAuthenticationFailureResponse('Invalid JWT Token');

        $dispatcher = $this->getEventDispatcherMock();
        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                Events::JWT_INVALID,
                new JWTInvalidEvent($authException, $expectedResponse)
            );

        $authenticator = new JWTTokenAuthenticator(
            $this->getJWTManagerMock(),
            $dispatcher,
            $this->getTokenExtractorMock()
        );

        $response = $authenticator->onAuthenticationFailure($this->getRequestMock(), $authException);

        $this->assertEquals($expectedResponse, $response);
        $this->assertSame($expectedResponse->getMessage(), $response->getMessage());
    }

    public function testStart()
    {
        $authException   = JWTAuthenticationException::tokenNotFound();
        $failureResponse = new JWTAuthenticationFailureResponse($authException->getMessage());

        $dispatcher = $this->getEventDispatcherMock();
        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                Events::JWT_NOT_FOUND,
                new JWTNotFoundEvent($authException, $failureResponse)
            );

        $authenticator = new JWTTokenAuthenticator(
            $this->getJWTManagerMock(),
            $dispatcher,
            $this->getTokenExtractorMock()
        );

        $response = $authenticator->start($this->getRequestMock(), $authException);

        $this->assertEquals($failureResponse, $response);
        $this->assertSame($failureResponse->getMessage(), $response->getMessage());
    }

    private function getJWTManagerMock($userIdentityField = null)
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
}
