<?php

namespace Autotask\Tests\Client;

use AidanCasey\MockClient\Client as MockClient;
use Autotask\Tests\Client\Factory\ClientFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ClientTest extends TestCase
{
    /**
     * @test
     */
    public function that_api_version_is_appended_if_not_present()
    {
        $httpClient = new MockClient();
        $client = ClientFactory::new($httpClient)
            ->baseUri('https://autotask.example.net')
            ->make();

        $client->get('Tickets');

        $httpClient
            ->assertUri('https://autotask.example.net/v1.0/Tickets');
    }

    /**
     * @test
     */
    public function that_api_version_is_not_appended_if_present()
    {
        $httpClient = new MockClient();
        $client = ClientFactory::new($httpClient)
            ->baseUri('https://autotask.example.net/v1.0/')
            ->make();

        $client->get('Tickets');

        $httpClient
            ->assertUri('https://autotask.example.net/v1.0/Tickets');
    }

    /**
     * @test
     */
    public function that_shorthand_delete_method_makes_delete_request()
    {
        $httpClient = new MockClient();
        $client = ClientFactory::new($httpClient)->make();

        $client->delete('Tickets');

        $httpClient->assertMethod('DELETE');
    }

    /**
     * @test
     */
    public function that_shorthand_get_method_makes_get_request()
    {
        $httpClient = new MockClient();
        $client = ClientFactory::new($httpClient)
            ->baseUri('https://autotask.example.net')
            ->make();

        $client->get('Tickets', [
            'firstName' => 'Jim',
            'lastName' => 'Halpert',
        ]);

        $httpClient
            ->assertMethod('GET')
            ->assertUri('https://autotask.example.net/v1.0/tickets?firstName=Jim&lastName=Halpert');
    }

    /**
     * @test
     */
    public function that_shorthand_patch_method_makes_patch_request()
    {
        $httpClient = new MockClient();
        $client = ClientFactory::new($httpClient)->make();

        $client->patch('Tickets', json_encode([
            'id' => 1,
            'firstName' => 'Jim',
        ]));

        $httpClient
            ->assertMethod('PATCH')
            ->assertBodyIs('{"id":1,"firstName":"Jim"}');
    }

    /**
     * @test
     */
    public function that_shorthand_post_method_makes_post_request()
    {
        $httpClient = new MockClient();
        $client = ClientFactory::new($httpClient)->make();

        $client->post('Tickets', json_encode([
            'id' => 1,
            'firstName' => 'Jim',
            'lastName' => 'Halpert',
        ]));

        $httpClient
            ->assertMethod('POST')
            ->assertBodyIs('{"id":1,"firstName":"Jim","lastName":"Halpert"}');
    }

    /**
     * @test
     */
    public function that_shorthand_put_method_makes_put_request()
    {
        $httpClient = new MockClient();
        $client = ClientFactory::new($httpClient)->make();

        $client->put('Tickets', json_encode([
            'id' => 1,
            'firstName' => 'Jim',
            'lastName' => 'Lars',
        ]));

        $httpClient
            ->assertMethod('PUT')
            ->assertBodyIs('{"id":1,"firstName":"Jim","lastName":"Lars"}');
    }

    /**
     * @test
     */
    public function that_headers_are_set()
    {
        $httpClient = new MockClient();
        $client = ClientFactory::new($httpClient)
            ->baseUri('https://autotask.example.net')
            ->username('jim.halpert@theoffice.com')
            ->secret('Abc123')
            ->integrationCode('Xyz123')
            ->make();

        $client->send('GET', 'Tickets/1');

        $httpClient
            ->assertMethod('GET')
            ->assertUri('https://autotask.example.net/v1.0/Tickets/1')
            ->assertHeaderEquals('APIIntegrationCode', 'Xyz123')
            ->assertHeaderEquals('Content-Type', 'application/json')
            ->assertHeaderEquals('Username', 'jim.halpert@theoffice.com')
            ->assertHeaderEquals('Secret', 'Abc123');
    }

    /**
     * @test
     */
    public function that_query_parameters_are_added()
    {
        $httpClient = new MockClient();
        $client = ClientFactory::new($httpClient)
            ->baseUri('https://autotask.example.net')
            ->make();

        $client->send('GET', 'Tickets/query', [
            'param1' => 'param1Value',
            'param2' => 'param2Value',
        ]);

        $httpClient
            ->assertMethod('GET')
            ->assertUri('https://autotask.example.net/v1.0/Tickets/query?param1=param1Value&param2=param2Value');
    }

    /**
     * @test
     */
    public function that_body_is_streamed()
    {
        $httpClient = new MockClient();
        $client = ClientFactory::new($httpClient)->make();

        $client->send(
            method: 'POST',
            endpoint: 'Tickets',
            body: json_encode([
                'id' => 1,
                'name' => 'Test',
            ]),
        );

        $httpClient
            ->assertMethod('POST')
            ->assertBodyIs('{"id":1,"name":"Test"}');
    }
}
