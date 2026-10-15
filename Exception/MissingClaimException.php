<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Exception;

use Throwable;

class MissingClaimException extends JWTFailureException
{
    public function __construct(
        string $claim,
        ?Throwable $previous = null
    ) {
        parent::__construct('missing_claim', sprintf('Missing required "%s" claim on JWT payload.', $claim), $previous);
    }
}
