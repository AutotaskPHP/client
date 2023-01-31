<?php

namespace Autotask\Tests\Client\Http\Request;

use AidanCasey\MockClient\Client;
use Autotask\Client\Http\Request\DeleteBuilder;
use Autotask\Tests\Client\Factory\ClientFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DeleteBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function that_delete_builder_is_made()
    {
        $client = ClientFactory::new()->make();

        $builder = DeleteBuilder::make($client, 'Tickets');

        $this->assertEquals(new DeleteBuilder($client, 'Tickets'), $builder);
    }

    /**
     * @test
     */
    public function that_delete_request_is_sent()
    {
        $httpClient = Client::fake([
            '*' => Client::response(__DIR__ . '/../../Stubs/write_response_successful.json'),
        ]);

        $client = ClientFactory::new($httpClient)
            ->baseUri('https://example.net/v1.0')
            ->make();

        DeleteBuilder::make($client, 'Tickets')->delete(1);

        $httpClient
            ->assertMethod('DELETE')
            ->assertUri('https://example.net/v1.0/Tickets/1');
    }
}
