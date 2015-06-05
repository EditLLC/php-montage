<?php

use Montage\Exceptions\MontageAuthException;
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
    protected $token = 'ab67d99c-c73d-4bdd-9879-33454d5c2095';

    /**
     * @param $subdomain
     * @param null $token
     * @param null $version
     * @return Montage
     */
    private function getMontage($subdomain, $token = null, $version = null)
    {
        return new Montage($subdomain, $token, $version);
    }

    /**
     * Useful for overloading specific methods for testing.
     *
     * @param array $methods
     * @return m\MockInterface
     */
    private function getMockedMontage($subdomain = 'foo', array $methodsBehaviors = [])
    {
        $methods = array_keys($methodsBehaviors);

        $montage = m::mock(
            sprintf('Montage\Montage[%s]', implode(',', $methods)),
            [$subdomain]
        );

        foreach ($methodsBehaviors as $method => $behavior)
        {
            $montage->shouldReceive($method)->andReturn($behavior);
        }

        return $montage;
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
        $schemaMock = m::mock('overload:Montage\Schema');
        $schemaMock->shouldReceive('__construct')->with('movies');
        $montage = $this->getMontage('foo', $this->token);
        $schema = $montage->movies();
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
        //mocked response
        $response = new stdClass;
        $response->data = new stdClass;
        $response->data->token = $this->token;

        $montage = $this->getMockedMontage('foo', ['request' => $response]);
        $this->assertNull($montage->token);
        $montage->auth('username', 'password');
        $this->assertEquals($montage->token, $this->token);
    }

}