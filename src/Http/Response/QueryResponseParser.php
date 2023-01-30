<?php

namespace Autotask\Client\Http\Response;

use Autotask\Client\Http\Response\Exception\UnexpectedResponseException;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;

final class QueryResponseParser
{
    public static function parse(ResponseInterface $response): Collection
    {
        /** @var array{items: null|array} $json */
        $json = json_decode($response->getBody()->getContents(), true);

        if (! isset($json['items'])) {
            throw new UnexpectedResponseException('Expecting `items` key in response.');
        }

        return new Collection($json['items']);
    }
}