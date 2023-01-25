<?php

namespace Autotask\Client;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class Client
{
    private readonly ClientInterface $client;

    private readonly RequestFactoryInterface $requestFactory;

    private readonly StreamFactoryInterface $streamFactory;

    private readonly string $baseUri;

    private readonly string $username;

    private readonly string $secret;

    private readonly string $integrationCode;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface  $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $baseUri,
        string $username,
        string $secret,
        string $integrationCode
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->baseUri = rtrim($baseUri, '/');
        $this->username = $username;
        $this->secret = $secret;
        $this->integrationCode = $integrationCode;
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function get(string $endpoint, array $query = []): ResponseInterface
    {
        return $this->send(
            method: 'GET',
            endpoint: $endpoint,
            query: $query
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function post(string $endpoint, ?string $body = null): ResponseInterface
    {
        return $this->send(
            method: 'POST',
            endpoint: $endpoint,
            body: $body
        );
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function send(
        string $method,
        string $endpoint,
        array $query = [],
        ?string $body = null,
        array $headers = []
    ): ResponseInterface {
        $request = $this->requestFactory->createRequest(
            method: $method,
            uri: $this->prepareEndpoint($endpoint, $query)
        );

        if ($body) {
            $request = $request->withBody(
                $this->streamFactory->createStream($body)
            );
        }

        $headers = array_replace_recursive($headers, [
            'APIIntegrationCode' => $this->integrationCode,
            'Content-Type' => 'application/json',
            'Username' => $this->username,
            'Secret' => $this->secret,
        ]);

        /**
         * @var string $headerName
         * @var array<array-key,string>|string $headerValue
         */
        foreach ($headers as $headerName => $headerValue) {
            $request = $request->withHeader($headerName, $headerValue);
        }

        return $this->client->sendRequest($request);
    }

    private function prepareEndpoint(string $endpoint, array $query = []): string
    {
        $endpoint = trim($endpoint, '/');
        $queryString = '';

        if ($query) {
            $queryString = '?' . http_build_query(data: $query, encoding_type: PHP_QUERY_RFC3986);
        }

        return "{$this->baseUri}/{$endpoint}{$queryString}";
    }
}