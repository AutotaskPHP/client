<?php

namespace Autotask\Tests\Client\Http\Response;

use AidanCasey\MockClient\Client;
use Autotask\Client\Http\Response\Exception\UnexpectedResponseException;
use Autotask\Client\Http\Response\FindResponseParser;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FindResponseParserTest extends TestCase
{
    /**
     * @test
     */
    public function that_an_exception_is_thrown_when_the_item_key_is_not_present()
    {
        $this->expectExceptionObject(new UnexpectedResponseException(
            'Expecting `item` key in response.'
        ));

        FindResponseParser::parse(
            Client::response(__DIR__ . '/../../Stubs/empty_response.json')
        );
    }

    /**
     * @test
     */
    public function that_item_is_returned()
    {
        $item = FindResponseParser::parse(
            Client::response(__DIR__ . '/../../Stubs/find_response_successful.json')
        );

        $this->assertEqualsCanonicalizing(
            ['id' => 1, 'ticketNumber' => 'XYZ123ABC'],
            $item
        );
    }
}
