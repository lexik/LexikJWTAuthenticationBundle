<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\Http\EntryPoint;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\EntryPoint\JWTEntryPoint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * JWTEntryPointTest.
 *
 * @group legacy
 *
 * @author Jérémie Augustin <jeremie.augustin@pixel-cookers.com>
 */
class JWTEntryPointTest extends TestCase
{
    /**
     * test start method.
     */
    public function testStart()
    {
        $entryPoint = new JWTEntryPoint();
        $this->assertInstanceOf(AuthenticationEntryPointInterface::class, $entryPoint);

        $response = $entryPoint->start($this->getRequest());
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode(), 'status code should be 401');

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($data['code'], '401');
        $this->assertEquals($data['message'], 'Bad credentials');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRequest()
    {
        return $this
            ->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
