<?php

namespace Autotask\Tests\Client\Http\Response;

use AidanCasey\MockClient\Client;
use Autotask\Client\Http\Paginator;
use Autotask\Client\Http\Response\Exception\UnexpectedResponseException;
use Autotask\Client\Http\Response\PagedResponseParser;
use Autotask\Tests\Client\Factory\ClientFactory;
use PHPUnit\Framework\TestCase;

class PagedResponseParserTest extends TestCase
{
    public function test_that_an_exception_is_thrown_when_the_items_key_is_not_present()
    {
        $this->expectExceptionObject(new UnexpectedResponseException(
            'Expecting `items` key in response.'
        ));

        PagedResponseParser::parse(
            ClientFactory::new()->make(),
            Client::response(__DIR__ . '/../../Stubs/empty_response.json')
        );
    }

    public function test_that_an_exception_is_thrown_when_the_page_details_key_is_not_present()
    {
        $this->expectExceptionObject(new UnexpectedResponseException(
            'Expecting `pageDetails` key in response.'
        ));

        PagedResponseParser::parse(
            ClientFactory::new()->make(),
            Client::response(__DIR__ . '/../../Stubs/query_response_without_page_details.json')
        );
    }

    public function test_that_paginator_is_returned()
    {
        $page = PagedResponseParser::parse(
            ClientFactory::new()->make(),
            Client::response(__DIR__ . '/../../Stubs/query_response_successful_page_1.json')
        );

        $this->assertInstanceOf(Paginator::class, $page);
    }
}