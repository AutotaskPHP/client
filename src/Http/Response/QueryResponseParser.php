<?php

namespace Autotask\Client\Http\Response;

use Autotask\Client\Http\Response\Exception\InvalidRequestException;
use Autotask\Client\Http\Response\Exception\UnexpectedResponseException;
use Psr\Http\Message\ResponseInterface;

final class QueryResponseParser
{
    /**
     * @param ResponseInterface $response
     * @return array{
     *     items: array<array-key, array>,
     *     pageDetails: array{count: int, nextPageUrl: null|string, prevPageUrl: null|string}
     * }
     */
    public static function parse(ResponseInterface $response): array
    {
        /**
         * @var array{
         *     errors?: array<array-key, string>,
         *     items: array<array-key, array>,
         *     pageDetails: array{count: int, nextPageUrl: null|string, prevPageUrl: null|string}
         * } $json
         */
        $json = json_decode($response->getBody()->getContents(), true);

        if (isset($json['errors'])) {
            throw InvalidRequestException::withErrors($json['errors']);
        }

        if (! isset($json['items'])) {
            throw new UnexpectedResponseException('Expecting `items` key in response.');
        }

        if (! isset($json['pageDetails'])) {
            throw new UnexpectedResponseException('Expecting `pageDetails` key in response.');
        }

        /**
         * @var array{
         *     items: array<array-key, array>,
         *     pageDetails: array{count: int, nextPageUrl: null|string, prevPageUrl: null|string}
         * } $json
         */
        return $json;
    }
}