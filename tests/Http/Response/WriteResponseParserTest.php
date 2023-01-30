<?php

namespace Autotask\Tests\Client\Http\Response;

use AidanCasey\MockClient\Client;
use Autotask\Client\Http\Response\Exception\InvalidRequestException;
use Autotask\Client\Http\Response\Exception\UnexpectedResponseException;
use Autotask\Client\Http\Response\WriteResponseParser;
use PHPUnit\Framework\TestCase;

class WriteResponseParserTest extends TestCase
{
    public function test_that_an_exception_is_thrown_when_errors_are_present()
    {
        $this->expectExceptionObject(InvalidRequestException::withErrors([
            'Some error 1.',
            'Some error 2.',
        ]));

        WriteResponseParser::parse(
            Client::response(__DIR__ . '/../../Stubs/write_response_unsuccessful_with_errors.json')
        );
    }

    public function test_that_an_exception_is_thrown_when_the_item_id_key_is_not_present()
    {
        $this->expectExceptionObject(new UnexpectedResponseException(
            'Expecting `itemId` key in response.'
        ));

        WriteResponseParser::parse(
            Client::response(__DIR__ . '/../../Stubs/empty_response.json')
        );
    }

    public function test_that_item_id_is_returned()
    {
        $itemId = WriteResponseParser::parse(
            Client::response(__DIR__ . '/../../Stubs/write_response_successful.json')
        );

        $this->assertSame(18, $itemId);
    }
}