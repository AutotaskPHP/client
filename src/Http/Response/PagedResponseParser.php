<?php

namespace Autotask\Client\Http\Response;

use Autotask\Client\Client;
use Autotask\Client\Http\Paginator;
use Autotask\Client\Http\Response\Exception\UnexpectedResponseException;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;

final class PagedResponseParser
{
    public static function parse(Client $client, ResponseInterface $response): Paginator
    {
        /** @var array{items: null|array<array-key,array>, pageDetails: null|array} $json */
        $json = json_decode($response->getBody()->getContents(), true);

        $items = self::parseItems($json);
        $pageDetails = self::parsePageDetails($json);

        return new Paginator(
            client: $client,
            items: $items,
            nextPageUrl: $pageDetails['nextPageUrl'] ?? null,
            previousPageUrl: $pageDetails['prevPageUrl'] ?? null,
        );
    }

    /**
     * @param array{items: null|array<array-key,array>, pageDetails: null|array} $response
     * @return Collection<array-key,array>
     */
    private static function parseItems(array $response): Collection
    {
        if (! isset($response['items'])) {
            throw new UnexpectedResponseException('Expecting `items` key in response.');
        }

        return new Collection($response['items']);
    }

    /**
     * @param array{items: null|array, pageDetails: null|array} $response
     * @return array{nextPageUrl: null|string, prevPageUrl: null|string}
     */
    private static function parsePageDetails(array $response): array
    {
        if (! isset($response['pageDetails'])) {
            throw new UnexpectedResponseException('Expecting `pageDetails` key in response.');
        }

        /**
         * @var array{nextPageUrl: null|string, prevPageUrl: null|string}
         */
        return $response['pageDetails'];
    }
}