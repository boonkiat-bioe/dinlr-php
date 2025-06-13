<?php
namespace Nava\Dinlr\Tests;

use Nava\Dinlr\Client;
use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Util\SharedCache;
use PHPUnit\Framework\TestCase;

class FloorplanTest extends TestCase
{
    protected $testConfig;
    protected $client;
    protected static $locationId;

    protected function setUp(): void
    {
        parent::setUp();

        echo "\n\n✅ TEST CASE: Floorplan API";
        echo "\n==============================================================";
        echo "\nSetting up test configuration...";
        
        $this->testConfig = require __DIR__ . '/config.php';
        $this->client     = new Client($this->testConfig);

        echo "\n• API URL: " . $this->testConfig['api_url'];
        echo "\n• Restaurant ID: " . $this->testConfig['restaurant_id'];
        echo "\n--------------------------------------------------------------";

        // Validate the test configuration
        $this->assertNotEmpty($this->testConfig['api_key'], 'API key is required for testing');
        $this->assertNotEmpty($this->testConfig['restaurant_id'], 'Restaurant ID is required for testing');
    }

    /**
     * Test getting all floorplans
     */
    public function testGetFloorplans()
    {
        echo "\n\nSTEP 1: Testing get floorplans";
        echo "\n--------------------------------------------------------------";

        try {
            // First verify we can connect to the API
            $restaurant = $this->client->restaurant()->get();
            $this->assertNotEmpty($restaurant->getId(), 'Failed to retrieve restaurant data');

            // Get a location first
            $locations = $this->client->locations()->list();
            SharedCache::$locations = $locations;
            $this->assertGreaterThan(0, count($locations), 'No locations found for testing');

            self::$locationId = $locations->first()->getId();
            $locationId = self::$locationId;
            echo "\n• Using location ID: " . $locationId;

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

            echo "\n✓ Floorplans retrieved successfully";
            echo "\n• Total Floorplans: " . count($floorplans);
            echo "\n• First Floorplan name: " . $floorplans->first()->getName();
            echo "\n--------------------------------------------------------------\n\n";
        } catch (ApiException $e) {
            $this->fail('API Exception: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')');
            echo "\n✗ Failed to retrieve floorplans: " . $e->getMessage();
            echo "\n--------------------------------------------------------------\n\n";
        }
    }

    /**
     * Test getting a single floorplan
     */
    public function testGetSingleFloorplan()
    {
        echo "\n\nSTEP 2: Testing get single floorplan";
        echo "\n--------------------------------------------------------------";
        try {
            $locationId = self::$locationId;
            echo "\n• Using location ID: " . $locationId;

            $floorplans = $this->client->floorplans()->list($locationId);

            if (count($floorplans) > 0) {
                $floorplanId = $floorplans->first()->getId();

                // Get single floorplan
                $floorplan = $this->client->floorplans()->get($floorplanId, $locationId);
                echo "\n• Floorplan ID: " . $floorplan->getId();

                $this->assertInstanceOf(\Nava\Dinlr\Models\Floorplan::class, $floorplan);
                $this->assertEquals($floorplanId, $floorplan->getId());
                $this->assertNotEmpty($floorplan->getName());
            } else {
                $this->markTestSkipped('No floorplans available for testing');
            }

            echo "\n• First Floorplan name: " . $floorplan->getName();
            echo "\n• First Floorplan id: " . $floorplan->getId();
            echo "\n\n✓ Single floorplan retrieved successfully";
            echo "\n--------------------------------------------------------------\n\n";
        } catch (ApiException $e) {
            $this->fail('API Exception: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')');
            echo "\n✗ Failed to retrieve single floorplan: " . $e->getMessage();
            echo "\n--------------------------------------------------------------\n\n";
        }
    }

    /**
     * Test error handling when an invalid location ID is used
     */
    public function testInvalidLocationId()
    {
        echo "\n\nSTEP 3: Testing invalid location ID";
        echo "\n--------------------------------------------------------------";
        try {
            $this->client->floorplans()->list('invalid_location_id');
        } 
        catch (ApiException $e) {
            echo "\n✓ Expected exception for invalid location ID is thrown.";
            echo "\n--------------------------------------------------------------\n\n";
            $this->assertInstanceOf(ApiException::class, $e); 
        }
    }
}
