<?php
namespace Nava\Dinlr\Tests;

use Nava\Dinlr\Client;
use Nava\Dinlr\Config;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * Test client creation
     */
    public function testClientCreation()
    {
        $config = new Config(['api_key' => 'test_key']);
        $client = new Client($config);

        $this->assertInstanceOf(Client::class, $client);
    }

    /**
     * Test client creation with array config
     */
    public function testClientCreationWithArrayConfig()
    {
        $client = new Client(['api_key' => 'test_key']);

        $this->assertInstanceOf(Client::class, $client);
    }

    /**
     * Test client creation with invalid config
     */
    public function testClientCreationWithInvalidConfig()
    {
        $this->expectException(\Nava\Dinlr\Exception\ConfigException::class);

        $client = new Client('invalid');
    }

    /**
     * Test resource access
     */
    public function testResourceAccess()
    {
        $client = new Client(['api_key' => 'test_key']);

        $this->assertInstanceOf(\Nava\Dinlr\Resources\Restaurant::class, $client->restaurant());
        $this->assertInstanceOf(\Nava\Dinlr\Resources\Customer::class, $client->customers());
        $this->assertInstanceOf(\Nava\Dinlr\Resources\Order::class, $client->orders());
    }
}
