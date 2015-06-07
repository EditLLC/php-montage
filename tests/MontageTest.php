<?php

use Montage\Montage;
use Mockery as m;

/**
 * Class MontageTest
 */
class MontageTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var string
     */
    protected $token = 'asd7f8-qewr89qwer-asfdlj2313-sfjl09j';

    /**
     * @param $subdomain
     * @param null $token
     * @param null $version
     * @return Montage
     */
    private function getMontage($subdomain, $token = null, $version = 1)
    {
        return new Montage($subdomain, $token, $version);
    }

    /**
     * Useful for overloading specific methods for testing.
     *
     * @param array $methods
     * @return m\MockInterface
     */
    private function getMockedMontage($subdomain = 'foo', array $methodsBehaviors = [], $token = null)
    {
        $methods = array_keys($methodsBehaviors);

        $montage = m::mock(
            sprintf('Montage\Montage[%s]', implode(',', $methods)),
            [$subdomain, $token]
        );

        foreach ($methodsBehaviors as $method => $behavior)
        {
            $montage->shouldReceive($method)->andReturn($behavior);
        }

        return $montage;
    }

    /**
     * Test fixture.
     *
     * @return m\MockInterface
     */
    public function montageWithMockedRequest()
    {
        //mocked response
        $response = new stdClass;
        $response->data = new stdClass;
        $response->data->token = $this->token;
        $subdomain = 'foo';

        return $this->getMockedMontage($subdomain, ['request' => $response]);
    }

    /**
     * Test that domains are set properly on the montage instance.
     */
    public function testDomains()
    {
        $montage = $this->getMontage('foo');
        $this->assertEquals($montage->domain, 'dev.montagehot.club');
        $this->assertEquals($montage->subdomain, 'foo');
    }

    /**
     * Test that token sent into the constructor are properly stored on
     * the montage instance.
     */
    public function testTokening()
    {
        $montage = $this->getMontage('foo', $this->token);
        $this->assertEquals($montage->token, $this->token);
    }

    /**
     * Test that api versions are properly stored on the montage instance.
     */
    public function testVersioning()
    {
        $version = 2;
        $montage = $this->getMontage('foo', $this->token, $version);
        $this->assertEquals($montage->version, $version);
    }

    /**
     * Test access to a schema object magically from the main
     * Montage class instance.
     */
    public function testMagicCallMethodCallsSchema()
    {
        //TODO: this isn't working, also probably needs teardown
        //$schemaMock = m::mock('overload:Montage\Schema');
        //$montage = $this->getMontage('foo', $this->token);
        //$schema = $montage->movies();
    }

    /**
     * Test that we can turn on debug mode as necessary.
     */
    public function testDebug()
    {
        $montage = $this->getMontage('foo');
        $montage->setDebug(true);
        $this->assertTrue($montage->debug);
        $montage->setDebug(false);
        $this->assertFalse($montage->debug);
    }

    /**
     * @expectedException Montage\Exceptions\MontageAuthException
     */
    public function testAuthWithInvalidParamsThrowsException()
    {
        $this->getMontage('foo')->auth();
    }

    public function testAuthWithValidParamsMakesRequest()
    {
        $montage = $this->montageWithMockedRequest();
        $this->assertNull($montage->token);
        $montage->auth('username', 'password');
        $this->assertEquals($montage->token, $this->token);
    }

    public function testGetUserWithoutTokenThrowsException()
    {
        $this->setExpectedException('Montage\Exceptions\MontageAuthException');
        $montage = $this->getMontage('foo');
        $montage->getUser();
    }

    /**
     * Test that the get user functions returns the result of a
     * request.
     */
    public function testGetUser()
    {
        $user = new stdClass;
        $montage = $this->getMockedMontage('foo', ['request' => $user], $this->token);
        $montageUser = $montage->getUser();

        $this->assertEquals($montageUser, $user);
    }

    /**
     * TODO: figure out why the exception isn't being propertly asserted.
     */
    public function testNoTokenResultsInException()
    {
//        $this->setExpectedException('Montage\Exceptions\MontageAuthException');
//        $montage = $this->getMontage('foo');
//        $montage->requireToken();
    }

    public function testUrlEndpoints()
    {
        $endpoints = [
            [['auth'], 'api/v1/auth/'],
            [['user'], 'api/v1/auth/user/'],
            [['schema-list'], 'api/v1/schemas/'],
            [['schema-detail', 'movies'], 'api/v1/schemas/movies/'],
            [['document-query', 'movies'], 'api/v1/schemas/movies/query/'],
            [['document-save', 'movies'], 'api/v1/schemas/movies/save/'],
            [['document-detail', 'foo', 'bar'], 'api/v1/schemas/foo/bar/'],
            [['file-list'], 'api/v1/files/'],
            [['file-detail', 'bleep'], 'api/v1/files/bleep/'],
        ];
        $montage = $this->getMontage('foo');

        foreach ($endpoints as $format)
        {
            $res = call_user_func_array([$montage, 'url'], $format[0]);
            $this->assertEquals($format[1], $res);
        }


    }

}