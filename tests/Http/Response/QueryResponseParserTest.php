<?php

namespace Autotask\Tests\Client\Http\Response;

use AidanCasey\MockClient\Client;
use Autotask\Client\Http\Response\Exception\UnexpectedResponseException;
use Autotask\Client\Http\Response\QueryResponseParser;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class QueryResponseParserTest extends TestCase
{
    /**
     * @test
     */
    public function that_an_exception_is_thrown_when_the_items_key_is_not_present()
    {
        $this->expectExceptionObject(new UnexpectedResponseException(
            'Expecting `items` key in response.'
        ));

        QueryResponseParser::parse(
            Client::response(__DIR__ . '/../../Stubs/empty_response.json')
        );
    }

    /**
     * @test
     */
    public function that_an_exception_is_thrown_when_the_page_details_key_is_not_present()
    {
        $this->expectExceptionObject(new UnexpectedResponseException(
            'Expecting `pageDetails` key in response.'
        ));

        QueryResponseParser::parse(
            Client::response(__DIR__ . '/../../Stubs/query_response_without_page_details.json')
        );
    }

    /**
     * @test
     */
    public function that_array_is_returned()
    {
        $items = QueryResponseParser::parse(
            Client::response(__DIR__ . '/../../Stubs/query_response_successful_page_1.json')
        );

        $this->assertEqualsCanonicalizing(
            [
                'items' => [
                    ['id' => 1, 'firstName' => 'Jim', 'lastName' => 'Halpert'],
                ],
                'pageDetails' => [
                    'count' => 1,
                    'nextPageUrl' => '/Contacts/query?page=2',
                    'prevPageUrl' => null,
                ],
            ],
            $items
        );
    }
}
