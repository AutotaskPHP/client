<?php

namespace Autotask\Client\Http\Response;

use Autotask\Client\Http\Response\Exception\InvalidRequestException;
use Autotask\Client\Http\Response\Exception\UnexpectedResponseException;
use Psr\Http\Message\ResponseInterface;

final class WriteResponseParser
{
    public static function parse(ResponseInterface $response): int
    {
        /** @var array{itemId: null|int, errors: null|array<array-key, string>} $json */
        $json = json_decode($response->getBody()->getContents(), true);

        if (isset($json['errors'])) {
            throw InvalidRequestException::withErrors($json['errors']);
        }

        if (! isset($json['itemId'])) {
            throw new UnexpectedResponseException('Expecting `itemId` key in response.');
        }

        /**
         * @var int
         */
        return $json['itemId'];
    }
}