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
     */
    const AUTHENTICATION_SUCCESS = 'lexik_jwt_authentication.on_authentication_success';

    /**
     * Dispatched after an authentication failure.
     * Hook into this event to add a custom error message in the response body.
     */
    const AUTHENTICATION_FAILURE = 'lexik_jwt_authentication.on_authentication_failure';

    /**
     * Dispatched before the token payload is encoded by the configured encoder (JWTEncoder by default).
     * Hook into this event to add extra fields to the payload.
     */
    const JWT_CREATED = 'lexik_jwt_authentication.on_jwt_created';

    /**
     * Dispatched right after token string is created.
     * Hook into this event to get token representation itself.
     */
    const JWT_ENCODED = 'lexik_jwt_authentication.on_jwt_encoded';

    /**
     * Dispatched after the token payload has been decoded by the configured encoder (JWTEncoder by default).
     * Hook into this event to perform additional validation on the received payload.
     */
    const JWT_DECODED = 'lexik_jwt_authentication.on_jwt_decoded';

    /**
     * Dispatched after the token payload has been authenticated by the provider.
     * Hook into this event to perform additional modification to the authenticated token using the payload.
     */
    const JWT_AUTHENTICATED = 'lexik_jwt_authentication.on_jwt_authenticated';

    /**
     * Dispatched after the token has been invalidated by the provider.
     * Hook into this event to add a custom error message in the response body.
     */
    const JWT_INVALID = 'lexik_jwt_authentication.on_jwt_invalid';

    /**
     * Dispatched when no token can be found in a request.
     * Hook into this event to set a custom response.
     */
    const JWT_NOT_FOUND = 'lexik_jwt_authentication.on_jwt_not_found';

    /**
     * Dispatched when the token is expired.
     * The expired token's payload can be retrieved by hooking into this event, so you can set a different
     * response.
     */
    const JWT_EXPIRED = 'lexik_jwt_authentication.on_jwt_expired';
}
