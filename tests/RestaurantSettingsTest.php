<?php
namespace Nava\Dinlr\Tests;

use Nava\Dinlr\Client;
use PHPUnit\Framework\TestCase;

class RestaurantSettingsTest extends TestCase
{
    /**
     * @var array
     */
    protected $testConfig;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        echo "\n\n✅ TEST CASE: Restaurant Settings API";
        echo "\n==============================================================";
        echo "\nSetting up test configuration...";

        // Load config
        $this->testConfig = require __DIR__ . '/config.php';

        echo "\n• API URL: " . $this->testConfig['api_url'];
        echo "\n• Restaurant ID: " . $this->testConfig['restaurant_id'];
        echo "\n--------------------------------------------------------------";
    }

    /**
     * Test getting restaurant details
     */
    public function testGetRestaurant()
    {
        echo "\n\nSTEP 1: Testing get restaurant details";
        echo "\n--------------------------------------------------------------";

        $client     = new Client($this->testConfig);
        $restaurant = $client->restaurant()->get();

        echo "\n• Restaurant ID: " . $restaurant->getId();
        echo "\n• Restaurant Name: " . $restaurant->getName();
        echo "\n• Currency: " . $restaurant->getCurrency();
        echo "\n✓ Restaurant details retrieved successfully";

        $this->assertNotEmpty($restaurant->getId());
        $this->assertNotEmpty($restaurant->getName());
        $this->assertNotEmpty($restaurant->getCurrency());
    }

    /**
     * Test getting all locations
     */
    public function testGetLocations()
    {
        echo "\n\nSTEP 2: Testing get all locations";
        echo "\n--------------------------------------------------------------";

        $client    = new Client($this->testConfig);
        $locations = $client->locations()->list();

        echo "\n• Total locations: " . count($locations);

        if (count($locations) > 0) {
            $location = $locations->first();
            echo "\n• First location ID: " . $location->getId();
            echo "\n• First location name: " . $location->getName();
        }

        echo "\n✓ Locations retrieved successfully";

        $this->assertInstanceOf(\Nava\Dinlr\Models\LocationCollection::class, $locations);
    }

    /**
     * Test getting all dining options
     */
    public function testGetDiningOptions()
    {
        echo "\n\nSTEP 3: Testing get all dining options";
        echo "\n--------------------------------------------------------------";

        $client = new Client($this->testConfig);

        // Use the first location ID if available
        $locationId = null;
        $locations  = $client->locations()->list();
        if (count($locations) > 0) {
            $locationId = $locations->first()->getId();
            echo "\n• Using location ID: " . $locationId;
        }

        $diningOptions = $client->diningOptions()->list($locationId);

        echo "\n• Total dining options: " . count($diningOptions);

        if (count($diningOptions) > 0) {
            $diningOption = $diningOptions->first();
            echo "\n• First dining option ID: " . $diningOption->getId();
            echo "\n• First dining option name: " . $diningOption->getName();
        }

        echo "\n✓ Dining options retrieved successfully";

        $this->assertInstanceOf(\Nava\Dinlr\Models\DiningOptionCollection::class, $diningOptions);
    }

    /**
     * Test getting all payment methods
     */
    public function testGetPaymentMethods()
    {
        echo "\n\nSTEP 4: Testing get all payment methods";
        echo "\n--------------------------------------------------------------";

        $client = new Client($this->testConfig);

        // Use the first location ID if available
        $locationId = null;
        $locations  = $client->locations()->list();
        if (count($locations) > 0) {
            $locationId = $locations->first()->getId();
            echo "\n• Using location ID: " . $locationId;
        }

        $paymentMethods = $client->paymentMethods()->list($locationId);

        echo "\n• Total payment methods: " . count($paymentMethods);

        if (count($paymentMethods) > 0) {
            $paymentMethod = $paymentMethods->first();
            echo "\n• First payment method ID: " . $paymentMethod->getId();
            echo "\n• First payment method name: " . $paymentMethod->getName();
        }

        echo "\n✓ Payment methods retrieved successfully";

        $this->assertInstanceOf(\Nava\Dinlr\Models\PaymentMethodCollection::class, $paymentMethods);
    }

    /**
     * Test getting all charges
     */
    public function testGetCharges()
    {
        echo "\n\nSTEP 5: Testing get all charges";
        echo "\n--------------------------------------------------------------";

        $client = new Client($this->testConfig);

        // Use the first location ID if available
        $locationId = null;
        $locations  = $client->locations()->list();
        if (count($locations) > 0) {
            $locationId = $locations->first()->getId();
            echo "\n• Using location ID: " . $locationId;
        }

        $charges = $client->charges()->list($locationId);

        echo "\n• Total charges: " . count($charges);

        if (count($charges) > 0) {
            $charge = $charges->first();
            echo "\n• First charge ID: " . $charge->getId();
            echo "\n• First charge name: " . $charge->getName();
        }

        echo "\n✓ Charges retrieved successfully";

        $this->assertInstanceOf(\Nava\Dinlr\Models\ChargeCollection::class, $charges);
    }

    /**
     * Test deprecated get settings endpoint
     */
    public function testGetSettings()
    {
        echo "\n\nSTEP 6: Testing deprecated get settings endpoint";
        echo "\n--------------------------------------------------------------";
        echo "\n⚠️ This test uses a deprecated endpoint that may be removed in future API versions.";

        $client = new Client($this->testConfig);

        // Make a direct request to the deprecated endpoint
        $response = $client->request('GET', '/' . $this->testConfig['restaurant_id'] . '/onlineorder/get-settings');

        echo "\n• Response data keys: " . implode(', ', array_keys($response['data'] ?? []));
        echo "\n✓ Settings retrieved successfully";

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
    }
}
