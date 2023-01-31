<?php

namespace Autotask\Client\Http\Request;

use Autotask\Client\Client;
use Autotask\Client\Http\Response\WriteResponseParser;

final class DeleteBuilder
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

    public function delete(int $id): int
    {
        $response = $this->client->delete("{$this->endpoint}/{$id}");

        return WriteResponseParser::parse($response);
    }
}
