<?php

namespace Mile6\LaravelEBMS\Tests\Unit;

use Mile6\LaravelEBMS\Facades\EBMS;
use Mile6\LaravelEBMS\Tests\TestCase;

class EBMSFacadeTest extends TestCase
{
    /** @test */
    public function clientGetsTheHostFromTheConfig()
    {
        $this->assertEquals('https://localhost', EBMS::getHost());
    }

    /** @test */
    public function clientGetsTheUsernameFromTheConfig()
    {
        $this->assertEquals('ebms', EBMS::getUsername());
    }

    /** @test */
    public function clientGetsThePasswordFromTheConfig()
    {
        $this->assertEquals('ebms-password', EBMS::getPassword());
    }

    /** @test */
    public function clientGetsTheHostFromTheUserSetValue()
    {
        EBMS::setHost('myhost');

        $this->assertEquals('https://myhost', EBMS::getHost());
    }

    /** @test */
    public function clientGetsTheUsernameFromTheUserSetValue()
    {
        EBMS::setUsername('myuser');

        $this->assertEquals('myuser', EBMS::getUsername());
    }

    /** @test */
    public function clientGetsThePasswordFromTheUserSetValue()
    {
        EBMS::setPassword('mypassword');

        $this->assertEquals('mypassword', EBMS::getPassword());
    }
}
