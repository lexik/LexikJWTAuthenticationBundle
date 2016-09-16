<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Guard;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

/**
 * FormLoginAuthenticator (Guard implementation).
 *
 * This authenticator provides JWT tokens from given credentials.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class FormLoginAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var AuthenticationSuccessHandlerInterface
     */
    private $authenticationSuccessHandler;

    /**
     * @var AuthenticationFailureHandlerInterface
     */
    private $authenticationFailureHandler;

    /**
     * @param UserPasswordEncoderInterface          $passwordEncoder
     * @param AuthenticationSuccessHandlerInterface $authenticationSuccessHandler
     * @param AuthenticationFailureHandlerInterface $authenticationFailureHandler
     */
    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        AuthenticationSuccessHandlerInterface $authenticationSuccessHandler,
        AuthenticationFailureHandlerInterface $authenticationFailureHandler
    ) {
        $this->passwordEncoder              = $passwordEncoder;
        $this->authenticationSuccessHandler = $authenticationSuccessHandler;
        $this->authenticationFailureHandler = $authenticationFailureHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        if ($this->getCheckPath() !== $request->getPathInfo()) {
            return;
        }

        $usernameParameter = $this->getUsernameParameter();
        $passwordParameter = $this->getPasswordParameter();

        return [
            $usernameParameter => $request->request->get($usernameParameter),
            $passwordParameter => $request->request->get($passwordParameter),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials[$this->getUsernameParameter()]);
    }

    /**
     * {@inheritdoc}
     *
     * @return JWTAuthenticationFailureResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $authException)
    {
        return $this->authenticationFailureHandler->onAuthenticationFailure($request, $authException);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return $this->authenticationSuccessHandler->onAuthenticationSuccess($request, $token);
    }

    /**
     * {@inheritdoc}
     *
     * @return JWTAuthenticationFailureResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return $this->onAuthenticationFailure($request, $authException ?: new AuthenticationException());
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $passwordKey = $this->getPasswordParameter();

        if (null === $credentials[$passwordKey] || null === $credentials[$this->getUsernameParameter()]) {
            throw new BadCredentialsException();
        }

        if (!$this->passwordEncoder->isPasswordValid($user, $credentials[$passwordKey])) {
            throw new BadCredentialsException();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * Returns the username key to retrieve from the current Request.
     *
     * Override this method for setting a custom one.
     *
     * @return string
     */
    protected function getUsernameParameter()
    {
        return '_username';
    }

    /**
     * Returns the password key to retrieve from the current Request.
     *
     * Override this method for setting a custom one.
     *
     * @return string
     */
    protected function getPasswordParameter()
    {
        return '_password';
    }

    /**
     * Returns the login check path.
     *
     * Override this method for setting a custom one.
     *
     * @return string
     */
    protected function getCheckPath()
    {
        return '/login_check';
    }
}
