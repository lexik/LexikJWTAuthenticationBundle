<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Services\JWSProvider;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\LcobucciJWSProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\RawKeyLoader;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\CreatedJWS;

/**
 * Tests the LcobucciJWSProvider.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class LcobucciJWSProviderTest extends AbstractJWSProviderTest
{
    protected static $providerClass = LcobucciJWSProvider::class;
    protected static $keyLoaderClass = RawKeyLoader::class;

    /**
     * First key is intended to fail for signature verification. Second one should work successfully.
     */
    protected static $additionalPublicKeys = ['
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAlO0GoAkE3uWugugPEGPF
TaYWzdkL2g8D9O3fX9pwOMxN2PxB8SLQNu7/25EwV+92DVZGKp9DoKc0L3gtBHkw
89cEjjG7R9CrQqNYRzXfaJMt9SZCW8u6B0r+vq90fFvZBexhrDZ+Qhjpbekmfyvw
b1+7gL5g/Kh2l1YmejE87oj4F+e/kNJpl0sb+akilsOmuVfn5vbv78S1J4Vur1/k
CVQcy6AE2Ii3b3e36rZDVKuAJYnAdHmxzYK762eWbc37GFh93H4OSV8NNAYNaxgZ
XT2CCWs7NXMLQa2pzVe/BBKaugq5LZbpIFtSJHenlhwFurUlakKYPSVf2rJLpvnj
1wIDAQAB
-----END PUBLIC KEY-----
', '
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAviDcvOMYwQzfVSknIUow
X2DsuCtXJ3aefBjSDluO6xMZGP9+3s5VfDh1OjwZ5vNx3tOxeMKHo2kDZSbyg//5
R90hisuQdQRJYbK3+DErpqyzWIoWmFcHOvBYYCpDN3pNj3NIbq6/ITUaRz5Iubqm
GvwcTCt1wvOBxlT1dlvvCEkLhg+pc2UZQVkaN1GxV8Lbc1VuZlK83ALaDArxrpYO
a6NgllshstrpnRvIIC9CIbblQ48Tl3yZQdX6OqF5jG6vV1J/e2vbWp8nZ9UuJqAO
z6RYcMJbgb3NVqup6fD4I2oKVhfw+kCm4yIadzet7mwiY3OUwY/ppRlo+/NrG/YG
tQIDAQAB
-----END PUBLIC KEY-----
'];

    protected static $multiplePublicKeysTestTokenPrivateKey = '
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEAviDcvOMYwQzfVSknIUowX2DsuCtXJ3aefBjSDluO6xMZGP9+
3s5VfDh1OjwZ5vNx3tOxeMKHo2kDZSbyg//5R90hisuQdQRJYbK3+DErpqyzWIoW
mFcHOvBYYCpDN3pNj3NIbq6/ITUaRz5IubqmGvwcTCt1wvOBxlT1dlvvCEkLhg+p
c2UZQVkaN1GxV8Lbc1VuZlK83ALaDArxrpYOa6NgllshstrpnRvIIC9CIbblQ48T
l3yZQdX6OqF5jG6vV1J/e2vbWp8nZ9UuJqAOz6RYcMJbgb3NVqup6fD4I2oKVhfw
+kCm4yIadzet7mwiY3OUwY/ppRlo+/NrG/YGtQIDAQABAoIBAADOduE+PV6kRVZB
JG9ZtzbQXHCzjl0WfbmdCVcSQFry68pVWx5q1aX/P1AN7TYzlioRz9DpUlX5HhCR
x7mTnmUVpQzHrEcyy3Tkoy8kVkEQvnoDq/DdfaRcXKObykkz9ZxEKux9RUZIyWG/
++L9zbKTmzMfhBJdohFLTvc/kJYCZ3Dq4pPE3Gy8Lr/J2RkVjM7GdyJ7U/5zN8ev
V8WTzXgPHEnIHQqwLHYAQtrCzI+vzTk84rViLI3Qnw8k0k50Ky1qtL/x7skw1aJe
16CVs9zjmqgVKMGPD0NqOf6pFVw8k5u5izxgXQWJAyLHgnvJdkVHL6gzruXCiqcJ
6YSuxwkCgYEA30yc5vnvHO+wSECbCZ2Xt9MyWX8vvhKhKXstig/MCBfqCIa/29Ze
foDb30CeYxYGGPz7XHd8R0Hd157tuRFyXn7NHJRFIes81PYWe0YtauUTY7DTq3yu
P3sug790QFE0Ba1EJIPPGCWXY/jnNwNFJYEf7DUSab0Zl1nhn0rh+vcCgYEA2fiw
AH2pYCTmEnNmpQbsnri/thpHZEkvwE9e029L0uLYVWXMPcGVJPbACuzG586UVXKt
jIR7xgnv9p9+2RcYSATUbVqNMBzbHcQvC86ly+Owrw10XHsZ2O5gOZuubO+8m2dO
T0xz9CD2wbxWqyJ0SY4bi6UKTNnIL/KV4zYZ1LMCgYAI/5xfDnldUCdpcfkNdSVV
ChTAWIjC/xsxgb4/Dw3o0ZXjzBJVOJlMPcMehwsa8RtDzIYQntwKPxRCiSwJRjO2
rSN04GC80i78YgJb62MPKLYUUV7mTTr0YfFo68EyilMvW2Egm5Mv5Ovp4nDm3aHe
tgpkSWs5iZ5LZBrcgXcD1wKBgQCVI1QBMfm23+hPjYgYuBEkVJJPndFlK7Ixad0e
29LMewu7+ofxZUeP4AjsMK+zoaPahzl5oJgzm08FtoGLNgMWG7/hBoj993BxAG+U
K5NDWwnj7FfGgy/fPtK19/AzdyDcT6XEGjJoQjmzuxKty0g3n7T3KjhO9t/C9r9Z
lAW+gwKBgQCpc5H6NoDyCO6faqfmsnCxi9waRMLqtlibzQcv+MrGRTIK2aEBlas4
sUx9pWIMozvKkxF+G/fkNIL7Z8eHA447Cg1Wy5F0mXJ57ufGZJ5B+SVMfaOr5Ekm
rT9kcwLvwUGRmm5HVAz06a9t6gOj0pvoR2oOn9GS7zWCxd3f8vL7nA==
-----END RSA PRIVATE KEY-----
';

    public function testCreateWithEcdsa()
    {
        $keyLoaderMock = $this->getKeyLoaderMock();
        $keyLoaderMock
            ->expects($this->once())
            ->method('loadKey')
            ->with('private')
            ->willReturn(<<<EOF
-----BEGIN EC PRIVATE KEY-----
MIHcAgEBBEIB+EYtmPtvg88MzxsRzgDGlKh+Z/iU99nmgKUjnw7+3eePeNQjaALU
DH+P7PNnF9nwfmQGTUBgQwtznmLAQcVdB3GgBwYFK4EEACOhgYkDgYYABAFp/WFf
W/TDCvI1o0GS1QJ2ZO8wYRIdV3VNVwnkFNiVeILY4jeq1lanQIBCswc+HHOuv1II
c+pDtNlumEvaA05RzwAGHve4mIi7RWaRQ2yAZfElRRV5f73h8eaG8qyNp6OtpuUO
TkeeWHzDF5tKLvuO0HGEX9N7Fn0dOBWZYVSDk/iaZw==
-----END EC PRIVATE KEY-----
EOF
        );
        $keyLoaderMock
            ->expects($this->once())
            ->method('getPassphrase')
            ->willReturn(null);

        $payload = ['username' => 'chalasr'];
        $jwsProvider = new LcobucciJWSProvider($keyLoaderMock, 'openssl', 'ES512', 3600, 0);

        $this->assertInstanceOf(CreatedJWS::class, $created = $jwsProvider->create($payload));

        return $created->getToken();
    }

    public function testMultiplePublicKeysSignatureCheckTestSuccess()
    {
        $keyLoaderMock = $this->getKeyLoaderMock();
        $keyLoaderMock
            ->expects($this->once())
            ->method('loadKey')
            ->with('public')
            ->willReturn(self::$publicKey);

        $keyLoaderMock
            ->method('getAdditionalPublicKeys')
            ->willReturn(self::$additionalPublicKeys);

        $jwsProvider = new self::$providerClass($keyLoaderMock, 'openssl', 'RS256', 3600, 0);
        $loadedJWS = $jwsProvider->load($this->createMultiplePublicKeysTestToken());

        $this->assertTrue($loadedJWS->isVerified());
    }

    public function testMultiplePublicKeysFails()
    {
        $keyLoaderMock = $this->getKeyLoaderMock();
        $keyLoaderMock
            ->expects($this->once())
            ->method('loadKey')
            ->with('public')
            ->willReturn(self::$publicKey);

        $keyLoaderMock
            ->method('getAdditionalPublicKeys')
            ->willReturn([self::$additionalPublicKeys[0]]); // Only try with the wrong additional public key

        $jwsProvider = new self::$providerClass($keyLoaderMock, 'openssl', 'RS256', 3600, 0);
        $loadedJWS = $jwsProvider->load($this->createMultiplePublicKeysTestToken());

        $this->assertFalse($loadedJWS->isVerified());
    }

    protected function createMultiplePublicKeysTestToken()
    {
        $keyLoaderMock = $this->getKeyLoaderMock();
        $keyLoaderMock
            ->expects($this->once())
            ->method('loadKey')
            ->with('private')
            ->willReturn(self::$multiplePublicKeysTestTokenPrivateKey);

        $payload = ['username' => 'chalasr', 'iat' => time()];
        $jwsProvider = new self::$providerClass($keyLoaderMock, 'openssl', 'RS256', 3600, 0);

        return $jwsProvider->create($payload)->getToken();
    }
}
