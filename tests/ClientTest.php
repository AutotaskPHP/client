<?php

namespace Autotask\Tests\Client;

use AidanCasey\MockClient\Client as MockClient;
use Autotask\Tests\Client\Factory\ClientFactory;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function test_shorthand_get_method()
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
            ->assertUri('https://autotask.example.net/tickets?firstName=Jim&lastName=Halpert');
    }

    public function test_shorthand_post_method()
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

    public function test_that_headers_are_set()
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
            ->assertUri('https://autotask.example.net/Tickets/1')
            ->assertHeaderEquals('APIIntegrationCode', 'Xyz123')
            ->assertHeaderEquals('Content-Type', 'application/json')
            ->assertHeaderEquals('Username', 'jim.halpert@theoffice.com')
            ->assertHeaderEquals('Secret', 'Abc123');
    }

    public function test_that_query_parameters_are_added()
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
            ->assertUri('https://autotask.example.net/Tickets/query?param1=param1Value&param2=param2Value');
    }

    public function test_that_body_is_streamed()
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