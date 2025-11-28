<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\WebToken;

use Jose\Bundle\JoseFramework\Services\ClaimCheckerManager;
use Jose\Bundle\JoseFramework\Services\ClaimCheckerManagerFactory;
use Jose\Bundle\JoseFramework\Services\HeaderCheckerManager;
use Jose\Bundle\JoseFramework\Services\JWELoader;
use Jose\Bundle\JoseFramework\Services\JWELoaderFactory;
use Jose\Bundle\JoseFramework\Services\JWSLoader;
use Jose\Bundle\JoseFramework\Services\JWSLoaderFactory;
use Jose\Component\Checker\InvalidClaimException;
use Jose\Component\Checker\MissingMandatoryClaimException;
use Jose\Component\Core\JWKSet;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;

final class AccessTokenLoader
{
    private $jwsLoader;
    private $jwsHeaderCheckerManager;
    private $claimCheckerManager;
    private $jweLoader;
    private $signatureKeyset;
    private $encryptionKeyset;

    /**
     * @var string[]
     */
    private $mandatoryClaims;

    private $continueOnDecryptionFailure;

    public function __construct(
        JWSLoaderFactory            $jwsLoaderFactory,
        ?JWELoaderFactory           $jweLoaderFactory,
        ClaimCheckerManagerFactory  $claimCheckerManagerFactory,
        array                       $claimChecker,
        array                       $jwsHeaderChecker,
        array                       $mandatoryClaims,
        array                       $signatureAlgorithms,
        string                      $signatureKeyset,
        ?bool                       $continueOnDecryptionFailure,
        ?array                      $jweHeaderChecker,
        ?array                      $keyEncryptionAlgorithms,
        ?array                      $contentEncryptionAlgorithms,
        ?string                     $encryptionKeyset
    ) {
        $this->jwsLoader = $jwsLoaderFactory->create(['jws_compact'], $signatureAlgorithms, $jwsHeaderChecker);
        if ($jweLoaderFactory !== null && !empty($keyEncryptionAlgorithms) && !empty($contentEncryptionAlgorithms) && !empty($jweHeaderChecker)) {
            $this->jweLoader = $jweLoaderFactory->create(['jwe_compact'], array_merge($keyEncryptionAlgorithms, $contentEncryptionAlgorithms), headerCheckers: $jweHeaderChecker);
            $this->continueOnDecryptionFailure = $continueOnDecryptionFailure;
        }
        $this->signatureKeyset = JWKSet::createFromJson($signatureKeyset);
        $this->encryptionKeyset = $encryptionKeyset ? JWKSet::createFromJson($encryptionKeyset) : null;
        $this->claimCheckerManager = $claimCheckerManagerFactory->create($claimChecker);
        $this->mandatoryClaims = $mandatoryClaims;
    }

    public function load(string $token): array
    {
        $token = $this->loadJWE($token);
        $data = $this->loadJWS($token);
        try {
            $this->claimCheckerManager->check($data, $this->mandatoryClaims);
        } catch (MissingMandatoryClaimException|InvalidClaimException $e) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, $e->getMessage(), $e, $data);
        } catch (\Throwable $e) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, 'Unable to load the token', $e, $data);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function loadJWS(string $token): array
    {
        $payload = null;
        $data = null;
        $signature = null;
        try {
            $jws = $this->jwsLoader->loadAndVerifyWithKeySet($token, $this->signatureKeyset, $signature);
        } catch (\Throwable $e) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, 'Invalid token. The token cannot be loaded or the signature cannot be verified.');
        }
        if ($signature !== 0) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, 'Invalid token. The token shall contain only one signature.');
        }

        $payload = $jws->getPayload();
        if (!$payload) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, 'Invalid payload. The token shall contain claims.');
        }

        $data = json_decode($payload, true);
        if (!is_array($data)) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, 'Invalid payload. The token shall contain claims.');
        }

        return $data;
    }

    private function loadJWE(string $token): string
    {
        if (!$this->jweLoader) {
            return $token;
        }

        $recipient = null;
        try {
            $jwe = $this->jweLoader->loadAndDecryptWithKeySet($token, $this->encryptionKeyset, $recipient);
        } catch (\Throwable $e) {
            if ($this->continueOnDecryptionFailure === true) {
                return $token;
            }
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, 'Invalid token. The token cannot be decrypted.', $e);
        }
        $token = $jwe->getPayload();
        if (!$token || $recipient !== 0) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, 'Invalid token. The token has no valid content.');
        }

        return $token;
    }
}
