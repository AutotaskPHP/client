<?php

namespace Autotask\Tests\Client;

use Autotask\Client\ClientManager;
use Autotask\Tests\Client\Factory\ClientFactory;
use PHPUnit\Framework\TestCase;

class ClientManagerTest extends TestCase
{
    public function test_that_clients_can_be_added_and_removed()
    {
        $client1 = ClientFactory::new()->make();
        $client2 = ClientFactory::new()->make();
        $client3 = ClientFactory::new()->make();

        ClientManager::add('client1', $client1);
        ClientManager::add('client2', $client2);
        ClientManager::add('client3', $client3);

        $this->assertSame($client1, ClientManager::get('client1'));
        $this->assertSame($client2, ClientManager::get('client2'));
        $this->assertSame($client3, ClientManager::get('client3'));

        $this->assertTrue(ClientManager::has('client1'));
        $this->assertTrue(ClientManager::has('client2'));
        $this->assertTrue(ClientManager::has('client3'));

        ClientManager::remove('client2');

        $this->assertFalse(ClientManager::has('client2'));
    }
}