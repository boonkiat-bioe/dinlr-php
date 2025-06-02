<?php
namespace Nava\Dinlr\Tests;

use Nava\Dinlr\Client;
use Nava\Dinlr\Exception\ApiException;
use PHPUnit\Framework\TestCase;

class FloorplanTest extends TestCase
{
    protected $testConfig;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testConfig = require __DIR__ . '/config.php';
        $this->client     = new Client($this->testConfig);

        // Validate the test configuration
        $this->assertNotEmpty($this->testConfig['api_key'], 'API key is required for testing');
        $this->assertNotEmpty($this->testConfig['restaurant_id'], 'Restaurant ID is required for testing');
    }

    /**
     * Test getting all floorplans
     */
    public function testGetFloorplans()
    {
        try {
            // First verify we can connect to the API
            $restaurant = $this->client->restaurant()->get();
            $this->assertNotEmpty($restaurant->getId(), 'Failed to retrieve restaurant data');

            // Get a location first
            $locations = $this->client->locations()->list();
            $this->assertGreaterThan(0, count($locations), 'No locations found for testing');

            $locationId = $locations->first()->getId();

            // Get floorplans for the location
            $floorplans = $this->client->floorplans()->list($locationId);

            $this->assertInstanceOf(\Nava\Dinlr\Models\FloorplanCollection::class, $floorplans);

            // If there are floorplans, test the data structure
            if (count($floorplans) > 0) {
                $floorplan = $floorplans->first();
                $this->assertNotEmpty($floorplan->getId());
                $this->assertNotEmpty($floorplan->getName());
                $this->assertIsArray($floorplan->getObjects());

                // Test objects structure if objects exist
                $objects = $floorplan->getObjects();
                if (! empty($objects)) {
                    $this->assertArrayHasKey('id', $objects[0]);
                    $this->assertArrayHasKey('name', $objects[0]);
                }
            } else {
                $this->markTestSkipped('No floorplans available for testing');
            }
        } catch (ApiException $e) {
            $this->fail('API Exception: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')');
        }
    }

    /**
     * Test getting a single floorplan
     */
    public function testGetSingleFloorplan()
    {
        try {
            // Get all floorplans first
            $locations = $this->client->locations()->list();
            $this->assertGreaterThan(0, count($locations), 'No locations found for testing');

            $locationId = $locations->first()->getId();
            $floorplans = $this->client->floorplans()->list($locationId);

            if (count($floorplans) > 0) {
                $floorplanId = $floorplans->first()->getId();

                // Get single floorplan
                $floorplan = $this->client->floorplans()->get($floorplanId);

                $this->assertInstanceOf(\Nava\Dinlr\Models\Floorplan::class, $floorplan);
                $this->assertEquals($floorplanId, $floorplan->getId());
                $this->assertNotEmpty($floorplan->getName());
            } else {
                $this->markTestSkipped('No floorplans available for testing');
            }
        } catch (ApiException $e) {
            $this->fail('API Exception: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')');
        }
    }

    /**
     * Test error handling when an invalid location ID is used
     */
    public function testInvalidLocationId()
    {
        $this->expectException(ApiException::class);
        $floorplans = $this->client->floorplans()->list('invalid_location_id');
    }
}
