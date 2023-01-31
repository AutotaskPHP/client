<?php

namespace Autotask\Tests\Client\Factory;

use Autotask\Client\Client;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class ClientFactory
{
    private array $attributes = [];

    public static function new(?ClientInterface $client = null): self
    {
        $factory = new self();

        if ($client) {
            $factory->client($client);
        }

        return $factory;
    }

    public function client(ClientInterface $client): self
    {
        $this->attributes['client'] = $client;

        return $this;
    }

    public function requestFactory(RequestFactoryInterface $requestFactory): self
    {
        $this->attributes['requestFactory'] = $requestFactory;

        return $this;
    }

    public function streamFactory(StreamFactoryInterface $streamFactory): self
    {
        $this->attributes['streamFactory'] = $streamFactory;

        return $this;
    }

    public function baseUri(string $baseUri): self
    {
        $this->attributes['baseUri'] = $baseUri;

        return $this;
    }

    public function username(string $username): self
    {
        $this->attributes['username'] = $username;

        return $this;
    }

    public function secret(string $secret): self
    {
        $this->attributes['secret'] = $secret;

        return $this;
    }

    public function integrationCode(string $integrationCode): self
    {
        $this->attributes['integrationCode'] = $integrationCode;

        return $this;
    }

    public function make(): Client
    {
        $attributes = array_replace_recursive([
            'client' => new \AidanCasey\MockClient\Client(),
            'requestFactory' => new RequestFactory(),
            'streamFactory' => new StreamFactory(),
            'baseUri' => 'autotask.example.net',
            'username' => 'jim.halpert@theoffice.com',
            'secret' => 'Abc123',
            'integrationCode' => 'Xyz123',
        ], $this->attributes);

        return new Client(...$attributes);
    }
}
