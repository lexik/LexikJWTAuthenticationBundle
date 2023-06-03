<?php

namespace Lexik\Bundle\JWTAuthenticationBundle;

/**
 * Events.
 *
 * @author Dev Lexik <dev@lexik.fr>
 */
final class Events
{
    /**
     * Dispatched after the token generation to allow sending more data
     * on the authentication success response.
     *
     * @Event("Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent")
     */
    public const AUTHENTICATION_SUCCESS = 'lexik_jwt_authentication.on_authentication_success';

    /**
     * Dispatched after an authentication failure.
     * Hook into this event to add a custom error message in the response body.
     *
     * @Event("Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent")
     */
    public const AUTHENTICATION_FAILURE = 'lexik_jwt_authentication.on_authentication_failure';

    /**
     * Dispatched before the token payload is encoded by the configured encoder (JWTEncoder by default).
     * Hook into this event to add extra fields to the payload.
     *
     * @Event("Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent")
     */
    public const JWT_CREATED = 'lexik_jwt_authentication.on_jwt_created';

    /**
     * Dispatched right after token string is created.
     * Hook into this event to get token representation itself.
     *
     * @Event("Lexik\Bundle\JWTAuthenticationBundle\Event\JWTEncodedEvent")
     */
    public const JWT_ENCODED = 'lexik_jwt_authentication.on_jwt_encoded';

    /**
     * Dispatched after the token payload has been decoded by the configured encoder (JWTEncoder by default).
     * Hook into this event to perform additional validation on the received payload.
     *
     * @Event("Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent")
     */
    public const JWT_DECODED = 'lexik_jwt_authentication.on_jwt_decoded';

    /**
     * Dispatched after the token payload has been authenticated by the provider.
     * Hook into this event to perform additional modification to the authenticated token using the payload.
     *
     * @Event("Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent")
     */
    public const JWT_AUTHENTICATED = 'lexik_jwt_authentication.on_jwt_authenticated';

    /**
     * Dispatched after the token has been invalidated by the provider.
     * Hook into this event to add a custom error message in the response body.
     *
     * @Event("Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent")
     */
    public const JWT_INVALID = 'lexik_jwt_authentication.on_jwt_invalid';

    /**
     * Dispatched when no token can be found in a request.
     * Hook into this event to set a custom response.
     *
     * @Event("Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent")
     */
    public const JWT_NOT_FOUND = 'lexik_jwt_authentication.on_jwt_not_found';

    /**
     * Dispatched when the token is expired.
     * The expired token's payload can be retrieved by hooking into this event, so you can set a different
     * response.
     *
     * @Event("Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent")
     */
    public const JWT_EXPIRED = 'lexik_jwt_authentication.on_jwt_expired';

    /**
     * Dispatched before the JWE is computed.
     * This event allow the JWE header parameters to be changed.
     * It is only dispatched when using Web-Token
     *
     * @Event("Lexik\Bundle\JWTAuthenticationBundle\Event\BeforeJWEComputationEvent")
     */
    public const BEFORE_JWE_COMPUTATION = 'lexik_jwt_authentication.before_jwe_computation';
}
