<?php

namespace Autotask\Tests\Client\Http\Request;

use AidanCasey\MockClient\Client;
use Autotask\Client\Http\Request\CreateBuilder;
use Autotask\Tests\Client\Factory\ClientFactory;
use PHPUnit\Framework\TestCase;

class CreateBuilderTest extends TestCase
{
    public function test_that_create_builder_is_made()
    {
        $client = ClientFactory::new()->make();

        $builder = CreateBuilder::make($client, 'Tickets');

        $this->assertEquals(new CreateBuilder($client, 'Tickets'), $builder);
    }

    public function test_that_create_request_is_sent()
    {
        $httpClient = Client::fake([
            '*' => Client::response(__DIR__ . '/../../Stubs/write_response_successful.json')
        ]);

        $client = ClientFactory::new($httpClient)
            ->baseUri('https://example.net/v1.0')
            ->make();

        CreateBuilder::make($client, 'Tickets')->post([
            'id' => 0,
            'ticketNumber' => 'ABC123XYZ',
        ]);

        $httpClient
            ->assertMethod('POST')
            ->assertUri('https://example.net/v1.0/Tickets')
            ->assertBodyIs('{"id":0,"ticketNumber":"ABC123XYZ"}');
    }
}