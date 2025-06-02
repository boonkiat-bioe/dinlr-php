<?php
namespace Nava\Dinlr\Tests;

use Nava\Dinlr\Client;
use Nava\Dinlr\Exception\ApiException;
use PHPUnit\Framework\TestCase;

class MaterialTest extends TestCase
{
    protected $testConfig;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        echo "\n\n✅ TEST CASE: Inventory Management API";
        echo "\n==============================================================";
        echo "\nSetting up test configuration...";

        $this->testConfig = require __DIR__ . '/config.php';
        $this->client     = new Client($this->testConfig);

        echo "\n• API URL: " . $this->testConfig['api_url'];
        echo "\n• Restaurant ID: " . $this->testConfig['restaurant_id'];
        echo "\n--------------------------------------------------------------";
    }

    public function testGetMaterials()
    {
        echo "\n\nSTEP 1: Testing get all materials";
        echo "\n--------------------------------------------------------------";

        try {
            $locations = $this->client->locations()->list();
            if (count($locations) === 0) {
                $this->markTestSkipped('No locations available');
                return;
            }

            $locationId = $locations->first()->getId();
            $materials  = $this->client->materials()->list($locationId);

            echo "\n• Total materials: " . count($materials);

            if (count($materials) > 0) {
                $material = $materials->first();
                echo "\n• First material ID: " . $material->getId();
                echo "\n• Name: " . $material->getName();
                echo "\n• SKU: " . ($material->getSku() ?: 'N/A');
                echo "\n• Unit: " . $material->getUnit();
                echo "\n• Has SKU: " . ($material->hasSku() ? 'Yes' : 'No');
            }

            // Test collection methods
            if (count($materials) > 0 && $materials->first()->hasSku()) {
                $foundBySku = $materials->findBySku($materials->first()->getSku());
                echo "\n• Found by SKU: " . ($foundBySku ? 'Yes' : 'No');
            }

            echo "\n✓ Materials retrieved successfully";

            $this->assertInstanceOf(\Nava\Dinlr\Models\MaterialCollection::class, $materials);

        } catch (ApiException $e) {
            if ($e->getCode() === 404) {
                $this->markTestSkipped('Materials not available: ' . $e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    public function testGetStockLevels()
    {
        echo "\n\nSTEP 2: Testing get stock levels";
        echo "\n--------------------------------------------------------------";

        try {
            $locations = $this->client->locations()->list();
            if (count($locations) === 0) {
                $this->markTestSkipped('No locations available');
                return;
            }

            $locationId = $locations->first()->getId();
            echo "\n• Getting stock levels for location: " . $locationId;

            $stockLevels = $this->client->materials()->getStockLevels($locationId);

            echo "\n• Total stock entries: " . count($stockLevels);
            echo "\n• Total quantity across all materials: " . $stockLevels->getTotalQuantity();

            if (count($stockLevels) > 0) {
                $stock = $stockLevels->first();
                echo "\n• First stock entry:";
                echo "\n  - Material ID: " . $stock->getMaterialId();
                echo "\n  - Quantity: " . $stock->getQuantity();
                echo "\n  - In stock: " . ($stock->isInStock() ? 'Yes' : 'No');
                echo "\n  - Updated at: " . $stock->getUpdatedAt();
            }

            // Test stock analysis
            $outOfStock = $stockLevels->getOutOfStock();
            echo "\n• Out of stock items: " . count($outOfStock);

            $lowStock = $stockLevels->getLowStock(10);
            echo "\n• Low stock items (≤10): " . count($lowStock);

            echo "\n✓ Stock levels retrieved successfully";

            $this->assertInstanceOf(\Nava\Dinlr\Models\MaterialStockCollection::class, $stockLevels);

        } catch (ApiException $e) {
            if ($e->getCode() === 404) {
                $this->markTestSkipped('Stock levels not available: ' . $e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    public function testGetStockTakes()
    {
        echo "\n\nSTEP 3: Testing get stock takes";
        echo "\n--------------------------------------------------------------";

        try {
            $stockTakes = $this->client->materials()->getStockTakes();

            echo "\n• Total stock takes: " . count($stockTakes);

            if (count($stockTakes) > 0) {
                $stockTake = $stockTakes->first();
                echo "\n• First stock take:";
                echo "\n  - ID: " . $stockTake->getId();
                echo "\n  - Location: " . $stockTake->getLocationId();
                echo "\n  - Start date: " . $stockTake->getStartDate();
                echo "\n  - End date: " . ($stockTake->getEndDate() ?: 'Ongoing');
                echo "\n  - Status: " . ($stockTake->isOngoing() ? 'Ongoing' : 'Completed');
                echo "\n  - Notes: " . ($stockTake->getNotes() ?: 'N/A');
                echo "\n  - Materials counted: " . $stockTake->getMaterialCount();
            }

            // Test collection methods
            $ongoing = $stockTakes->getOngoing();
            echo "\n• Ongoing stock takes: " . count($ongoing);

            $completed = $stockTakes->getCompleted();
            echo "\n• Completed stock takes: " . count($completed);

            echo "\n✓ Stock takes retrieved successfully";

            $this->assertInstanceOf(\Nava\Dinlr\Models\StockTakeCollection::class, $stockTakes);

        } catch (ApiException $e) {
            if ($e->getCode() === 404) {
                $this->markTestSkipped('Stock takes not available: ' . $e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    public function testStockTakeDetails()
    {
        echo "\n\nSTEP 4: Testing stock take details";
        echo "\n--------------------------------------------------------------";

        try {
            $stockTakes = $this->client->materials()->getStockTakes();

            // Find a completed stock take with materials
            $detailedStockTake = null;
            foreach ($stockTakes as $stockTake) {
                if ($stockTake->isCompleted() && $stockTake->getMaterialCount() > 0) {
                    $detailedStockTake = $stockTake;
                    break;
                }
            }

            if (! $detailedStockTake) {
                $this->markTestSkipped('No completed stock takes with materials found');
                return;
            }

            echo "\n• Analyzing stock take: " . $detailedStockTake->getId();
            echo "\n• Duration: " . round($detailedStockTake->getDuration() / 3600, 2) . " hours";

            $materials = $detailedStockTake->getMaterials();
            echo "\n• Materials in stock take:";

            foreach (array_slice($materials, 0, 3) as $material) {
                echo "\n  - Material " . $material['material'] . ": " . $material['counted'] . " units";
            }

            if (count($materials) > 3) {
                echo "\n  ... and " . (count($materials) - 3) . " more materials";
            }

            echo "\n✓ Stock take details analyzed successfully";

        } catch (ApiException $e) {
            $this->markTestSkipped('Stock take details not available: ' . $e->getMessage());
        }
    }

    public function testStockAnalysis()
    {
        echo "\n\nSTEP 5: Testing inventory analysis";
        echo "\n--------------------------------------------------------------";

        try {
            $locations = $this->client->locations()->list();
            if (count($locations) === 0) {
                $this->markTestSkipped('No locations available');
                return;
            }

            $locationId = $locations->first()->getId();

            // Get materials and stock levels
            $materials   = $this->client->materials()->list($locationId);
            $stockLevels = $this->client->materials()->getStockLevels($locationId);

            echo "\n• Inventory Analysis:";
            echo "\n• Total SKUs: " . count($materials);
            echo "\n• Total stock entries: " . count($stockLevels);
            echo "\n• Stock coverage: " . round((count($stockLevels) / max(count($materials), 1)) * 100, 1) . "%";

            // Analyze by unit type
            $unitTypes = [];
            foreach ($materials as $material) {
                $unit = $material->getUnit();
                if (! isset($unitTypes[$unit])) {
                    $unitTypes[$unit] = 0;
                }
                $unitTypes[$unit]++;
            }

            echo "\n• Materials by unit type:";
            foreach ($unitTypes as $unit => $count) {
                echo "\n  - " . $unit . ": " . $count . " items";
            }

            echo "\n✓ Inventory analysis completed";

        } catch (ApiException $e) {
            $this->markTestSkipped('Inventory analysis not available: ' . $e->getMessage());
        }
    }
}
