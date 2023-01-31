<?php

namespace Autotask\Client\Http\Response;

use Autotask\Client\Http\Response\Exception\UnexpectedResponseException;
use Psr\Http\Message\ResponseInterface;

final class FindResponseParser
{
    public static function parse(ResponseInterface $response): array
    {
        /** @var array{item: null|array} $json */
        $json = json_decode($response->getBody()->getContents(), true);

        if (! isset($json['item'])) {
            throw new UnexpectedResponseException('Expecting `item` key in response.');
        }

        return $json['item'];
    }
}
