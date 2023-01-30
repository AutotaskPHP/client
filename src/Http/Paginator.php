<?php

namespace Autotask\Client\Http;

use Autotask\Client\Client;
use Autotask\Client\Http\Response\PagedResponseParser;
use Illuminate\Support\Collection;
use LogicException;

final class Paginator
{
    /**
     * @var Collection<array-key,array> $items
     */
    public readonly Collection $items;

    private readonly Client $client;

    private ?string $nextPageUrl = null;

    private ?string $previousPageUrl = null;

    /**
     * @param Collection<array-key,array> $items
     */
    public function __construct(Client $client, Collection $items, ?string $nextPageUrl, ?string $previousPageUrl)
    {
        $this->client = $client;
        $this->items = $items;
        $this->setNextPage($nextPageUrl);
        $this->setPreviousPage($previousPageUrl);
    }

    public function hasNextPage(): bool
    {
        return isset($this->nextPageUrl);
    }

    public function hasPreviousPage(): bool
    {
        return isset($this->previousPageUrl);
    }

    public function nextPage(): Paginator
    {
        if (! isset($this->nextPageUrl)) {
            throw new LogicException('There is no next page.');
        }

        $response = $this->client->get($this->nextPageUrl);

        return PagedResponseParser::parse($this->client, $response);
    }

    public function previousPage(): Paginator
    {
        if (! isset($this->previousPageUrl)) {
            throw new LogicException('There is no previous page.');
        }

        $response = $this->client->get($this->previousPageUrl);

        return PagedResponseParser::parse($this->client, $response);
    }

    private function setNextPage(?string $nextPage): void
    {
        if (! $nextPage) {
            return;
        }

        preg_match('#\w+/query.*#', $nextPage, $nextPageMatches);

        if (! isset($nextPageMatches[0])) {
            return;
        }

        $this->nextPageUrl = $nextPageMatches[0];
    }

    private function setPreviousPage(?string $previousPage): void
    {
        if (! $previousPage) {
            return;
        }

        preg_match('#\w+/query.*#', $previousPage, $previousPageMatches);

        if (! isset($previousPageMatches[0])) {
            return;
        }

        $this->previousPageUrl = $previousPageMatches[0];
    }
}