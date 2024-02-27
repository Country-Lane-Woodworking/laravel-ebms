<?php

namespace Mile6\LaravelEBMS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class EBMS
{
    protected $host, $username, $password, $withAuth = true;

    public function getUsername()
    {
        return $this->username ?? config('ebms.api.username');
    }

    public function getPassword()
    {
        return $this->password ?? config('ebms.api.password');
    }

    public function getHost()
    {
        return Str::start($this->host ?? config('ebms.api.host'), 'https://');
    }

    public function shouldSendAuthentication()
    {
        return $this->withAuth;
    }

    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function setAuthentication($username, $password)
    {
        $this->username = $username;
        $this->password = $password;

        return $this;
    }

    public function withoutAuth()
    {
        $this->withAuth = false;

        return $this;
    }

    public function withAuth()
    {
        $this->withAuth = true;

        return $this;
    }

    public function get($url)
    {
        return $this->makeRequest('GET', $url);
    }

    public function post($url, $body)
    {
        return $this->makeRequest('POST', $url, $body);
    }

    public function patch($url, $body)
    {
        return $this->makeRequest('PATCH', $url, $body);
    }

    public function put($url, $body)
    {
        return $this->makeRequest('PUT', $url, $body);
    }

    public function delete($url)
    {
        return $this->makeRequest('DELETE', $url);
    }

    public function makeRequest($method, $url, $body = [])
    {
        $request = Http::contentType('application/json');

        if (!Str::startsWith($url, ['http://', 'https://'])) {
            $url = rtrim($this->getHost(), '/') . Str::start($url, '/');
        }

        if ($this->shouldSendAuthentication()) {
            $request->withBasicAuth($this->getUsername(), $this->getPassword());
        }

        if (!empty($body)) {
            $request->withBody(json_encode($body), 'application/json');
        }

        $response = $request->send($method, $url);

        return $response->json();
    }
}
