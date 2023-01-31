<?php

namespace Autotask\Tests\Client\Http\Request;

use AidanCasey\MockClient\Client;
use Autotask\Client\Http\Request\FindBuilder;
use Autotask\Tests\Client\Factory\ClientFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FindBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function that_find_builder_is_made()
    {
        $client = ClientFactory::new()->make();

        $builder = FindBuilder::make($client, 'Tickets');

        $this->assertEquals(new FindBuilder($client, 'Tickets'), $builder);
    }

    /**
     * @test
     */
    public function that_find_request_is_sent()
    {
        $httpClient = Client::fake([
            '*' => Client::response(__DIR__ . '/../../Stubs/find_response_successful.json'),
        ]);

        $client = ClientFactory::new($httpClient)
            ->baseUri('https://example.net/v1.0')
            ->make();

        FindBuilder::make($client, 'Tickets')->find(1);

        $httpClient
            ->assertMethod('GET')
            ->assertUri('https://example.net/v1.0/Tickets/1');
    }
}
