<?php

namespace Mile6\LaravelEBMS\Tests\Unit;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mile6\LaravelEBMS\EBMS;
use Mile6\LaravelEBMS\Tests\TestCase;

class EBMSTest extends TestCase
{

    /** @test */
    public function clientGetsTheHostFromTheConfig()
    {
        $ebms = app(EBMS::class);

        $this->assertEquals('https://localhost', $ebms->getHost());
    }

    /** @test */
    public function clientGetsTheUsernameFromTheConfig()
    {
        $ebms = app(EBMS::class);

        $this->assertEquals('ebms', $ebms->getUsername());
    }

    /** @test */
    public function clientGetsThePasswordFromTheConfig()
    {
        $ebms = app(EBMS::class);

        $this->assertEquals('ebms-password', $ebms->getPassword());
    }

    /** @test */
    public function clientGetsTheHostFromTheUserSetValue()
    {
        $ebms = app(EBMS::class);

        $ebms->setHost('myhost');

        $this->assertEquals('https://myhost', $ebms->getHost());
    }

    /** @test */
    public function clientGetsTheUsernameFromTheUserSetValue()
    {
        $ebms = app(EBMS::class);

        $ebms->setUsername('myuser');

        $this->assertEquals('myuser', $ebms->getUsername());
    }

    /** @test */
    public function clientGetsThePasswordFromTheUserSetValue()
    {
        $ebms = app(EBMS::class);

        $ebms->setPassword('mypassword');

        $this->assertEquals('mypassword', $ebms->getPassword());
    }

    /** @test */
    public function clientGetsTheUsernameAndPasswordFromTheUserSetValue()
    {
        $ebms = app(EBMS::class);

        $ebms->setAuthentication('myusername', 'mypassword');

        $this->assertEquals('myusername', $ebms->getUsername());
        $this->assertEquals('mypassword', $ebms->getPassword());
    }

    /** @test */
    public function clientCanDisableSendingAuthenticationWithRequest()
    {
        $ebms = app(EBMS::class);

        $ebms->withoutAuth();

        $this->assertFalse($ebms->shouldSendAuthentication());
    }

    /** @test */
    public function clientCanEnableSendingAuthenticationWithRequest()
    {
        $ebms = app(EBMS::class);

        $ebms->withAuth();

        $this->assertTrue($ebms->shouldSendAuthentication());
    }

    /** @test */
    public function clientCanMakeGETRequest()
    {
        Http::fake([
            '*' => Http::response('Hello World', 200)
        ]);

        $ebms = app(EBMS::class);

        $ebms->get('http://localhost/');

        Http::assertSent(function (Request $request) {
            return $request->url() == 'http://localhost/' &&
                $request->method() === 'GET';
        });
    }

    /** @test */
    public function clientCanMakeGETRequestsWithAuthentication()
    {
        Http::fake([
            '*' => Http::response('Hello World', 200)
        ]);

        $ebms = app(EBMS::class);

        $ebms->get('http://localhost/');

        Http::assertSent(function (Request $request) {
            return $request->url() == 'http://localhost/' &&
                $request->method() === 'GET' &&
                $request->hasHeader('Authorization') &&
                Str::contains(Arr::first($request->header('Authorization')), 'Basic');
        });
    }

    /** @test */
    public function clientCanMakeGETRequestsWithoutAuthentication()
    {
        Http::fake([
            '*' => Http::response('Hello World', 200)
        ]);

        $ebms = app(EBMS::class);

        $ebms->withoutAuth()->get('http://localhost/');

        Http::assertSent(function (Request $request) {
            return $request->url() == 'http://localhost/' &&
                $request->method() === 'GET' &&
                !$request->hasHeader('Authorization');
        });
    }

    /** @test */
    public function clientCanMakePOSTRequest()
    {
        Http::fake([
            '*' => Http::response('Hello World', 200)
        ]);

        $ebms = app(EBMS::class);

        $ebms->post('http://localhost/');

        Http::assertSent(function (Request $request) {
            return $request->url() == 'http://localhost/' &&
                $request->method() === 'POST';
        });
    }

    /** @test */
    public function clientCanMakePATCHRequest()
    {
        Http::fake([
            '*' => Http::response('Hello World', 200)
        ]);

        $ebms = app(EBMS::class);

        $ebms->patch('http://localhost/');

        Http::assertSent(function (Request $request) {
            return $request->url() == 'http://localhost/' &&
                $request->method() === 'PATCH';
        });
    }

    /** @test */
    public function clientCanMakePUTRequest()
    {
        Http::fake([
            '*' => Http::response('Hello World', 200)
        ]);

        $ebms = app(EBMS::class);

        $ebms->put('http://localhost/');

        Http::assertSent(function (Request $request) {
            return $request->url() == 'http://localhost/' &&
                $request->method() === 'PUT';
        });
    }

    /** @test */
    public function clientCanMakeDELETERequest()
    {
        Http::fake([
            '*' => Http::response('Hello World', 200)
        ]);

        $ebms = app(EBMS::class);

        $ebms->delete('http://localhost/');

        Http::assertSent(function (Request $request) {
            return $request->url() == 'http://localhost/' &&
                $request->method() === 'DELETE';
        });
    }

    /** @test */
    public function clientCanUseJustAnURI()
    {
        Http::fake([
            '*' => Http::response('Hello World', 200)
        ]);

        $ebms = app(EBMS::class);

        $ebms->get('/hello');

        Http::assertSent(function (Request $request) {
            return $request->url() == 'https://localhost/hello' &&
                $request->method() === 'GET';
        });
    }
}
