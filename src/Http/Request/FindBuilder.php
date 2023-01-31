<?php

namespace Autotask\Client\Http\Request;

use Autotask\Client\Client;
use Autotask\Client\Http\Response\FindResponseParser;

final class FindBuilder
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

    public function find(int $id): array
    {
        $response = $this->client->get("{$this->endpoint}/{$id}");

        return FindResponseParser::parse($response);
    }
}
