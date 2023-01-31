<?php

namespace Autotask\Tests\Client\Http\Request;

use AidanCasey\MockClient\Client;
use Autotask\Client\Http\Request\UpdateBuilder;
use Autotask\Tests\Client\Factory\ClientFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class UpdateBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function that_update_builder_is_made()
    {
        $client = ClientFactory::new()->make();

        $builder = UpdateBuilder::make($client, 'Tickets');

        $this->assertEquals(new UpdateBuilder($client, 'Tickets'), $builder);
    }

    /**
     * @test
     */
    public function that_patch_request_is_sent()
    {
        $httpClient = Client::fake([
            '*' => Client::response(__DIR__ . '/../../Stubs/write_response_successful.json'),
        ]);

        $client = ClientFactory::new($httpClient)
            ->baseUri('https://example.net/v1.0')
            ->make();

        UpdateBuilder::make($client, 'Tickets')
            ->patch(18, [
                'ticketNumber' => 'BCD123',
            ]);

        $httpClient
            ->assertMethod('PATCH')
            ->assertUri('https://example.net/v1.0/Tickets/18')
            ->assertBodyIs('{"ticketNumber":"BCD123"}');
    }

    /**
     * @test
     */
    public function that_put_request_is_sent()
    {
        $httpClient = Client::fake([
            '*' => Client::response(__DIR__ . '/../../Stubs/write_response_successful.json'),
        ]);

        $client = ClientFactory::new($httpClient)
            ->baseUri('https://example.net/v1.0')
            ->make();

        UpdateBuilder::make($client, 'Tickets')
            ->put(18, [
                'ticketNumber' => 'BCD123',
            ]);

        $httpClient
            ->assertMethod('PUT')
            ->assertUri('https://example.net/v1.0/Tickets/18')
            ->assertBodyIs('{"ticketNumber":"BCD123"}');
    }
}
