<?php

namespace Autotask\Client\Http\Request;

use Autotask\Client\Client;
use Autotask\Client\Http\Response\WriteResponseParser;

final class CreateBuilder
{
    private readonly Client $client;

    private readonly string $endpoint;

    public static function make(Client $client, string $endpoint): self
    {
        return new self($client, $endpoint);
    }

    public function __construct(Client $client, string $endpoint)
    {
        $this->client = $client;
        $this->endpoint = trim($endpoint, '/');
    }

    public function post(array $attributes): int
    {
        $response = $this->client->post($this->endpoint, json_encode($attributes));

        return WriteResponseParser::parse($response);
    }
}
