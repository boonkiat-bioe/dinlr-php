<?php
namespace Nava\Dinlr\Tests;

use Nava\Dinlr\Client;
use PHPUnit\Framework\TestCase;

class DiscountPromotionTest extends TestCase
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

        echo "\n\n✅ TEST CASE: Discounts & Promotions API";
        echo "\n==============================================================";
        echo "\nSetting up test configuration...";

        // Load config
        $this->testConfig = require __DIR__ . '/config.php';

        echo "\n• API URL: " . $this->testConfig['api_url'];
        echo "\n• Restaurant ID: " . $this->testConfig['restaurant_id'];
        echo "\n--------------------------------------------------------------";
    }

    /**
     * Test getting all discounts
     */
    public function testGetDiscounts()
    {
        echo "\n\nSTEP 1: Testing get all discounts";
        echo "\n--------------------------------------------------------------";

        $client = new Client($this->testConfig);

        // Use the first location ID if available
        $locationId = null;
        $locations  = $client->locations()->list();
        if (count($locations) > 0) {
            $locationId = $locations->first()->getId();
            echo "\n• Using location ID: " . $locationId;
        }

        $discounts = $client->discounts()->list($locationId);

        echo "\n• Total discounts: " . count($discounts);

        if (count($discounts) > 0) {
            $discount = $discounts->first();
            echo "\n• First discount ID: " . $discount->getId();
            echo "\n• First discount name: " . $discount->getName();
            echo "\n• Type: " . $discount->getType();
            echo "\n• Stackable: " . ($discount->isStackable() ? "Yes" : "No");
            echo "\n• Manufacturer discount: " . ($discount->isManufacturerDiscount() ? "Yes" : "No");

            if ($discount->getValue() !== null) {
                echo "\n• Value: " . $discount->getValue();
                if ($discount->isPercentDiscount()) {
                    echo "%";
                }
            } else {
                echo "\n• Value: Open discount (custom value)";
            }

            if ($discount->getMaxValue() !== null) {
                echo "\n• Max value: " . $discount->getMaxValue();
            }
        }

        echo "\n✓ Discounts retrieved successfully";

        $this->assertInstanceOf(\Nava\Dinlr\Models\DiscountCollection::class, $discounts);
    }

    /**
     * Test getting a single discount
     */
    public function testGetSingleDiscount()
    {
        echo "\n\nSTEP 2: Testing get single discount";
        echo "\n--------------------------------------------------------------";

        $client = new Client($this->testConfig);

        // First get all discounts
        $locationId = null;
        $locations  = $client->locations()->list();
        if (count($locations) > 0) {
            $locationId = $locations->first()->getId();
        }

        $discounts = $client->discounts()->list($locationId);

        if (count($discounts) > 0) {
            $discountId = $discounts->first()->getId();
            echo "\n• Testing with discount ID: " . $discountId;

            $discount = $client->discounts()->get($discountId);

            echo "\n• Discount name: " . $discount->getName();
            echo "\n• Type: " . $discount->getType();
            echo "\n• Is percent discount: " . ($discount->isPercentDiscount() ? "Yes" : "No");
            echo "\n• Is price discount: " . ($discount->isPriceDiscount() ? "Yes" : "No");
            echo "\n• Is open discount: " . ($discount->isOpenDiscount() ? "Yes" : "No");
            echo "\n✓ Single discount retrieved successfully";

            $this->assertInstanceOf(\Nava\Dinlr\Models\Discount::class, $discount);
            $this->assertEquals($discountId, $discount->getId());
        } else {
            $this->markTestSkipped('No discounts available for testing');
        }
    }

    /**
     * Test getting all promotions
     */
    public function testGetPromotions()
    {
        echo "\n\nSTEP 3: Testing get all promotions";
        echo "\n--------------------------------------------------------------";

        $client = new Client($this->testConfig);

        // Use the first location ID if available
        $locationId = null;
        $locations  = $client->locations()->list();
        if (count($locations) > 0) {
            $locationId = $locations->first()->getId();
            echo "\n• Using location ID: " . $locationId;
        }

        $promotions = $client->promotions()->list($locationId);

        echo "\n• Total promotions: " . count($promotions);

        if (count($promotions) > 0) {
            $promotion = $promotions->first();
            echo "\n• First promotion ID: " . $promotion->getId();
            echo "\n• First promotion name: " . $promotion->getName();
            echo "\n• Summary: " . ($promotion->getSummary() ?: 'N/A');
            echo "\n• Type: " . $promotion->getType();
            echo "\n• Start date: " . $promotion->getStartDate();
            echo "\n• End date: " . ($promotion->getEndDate() ?: 'No end date');
            echo "\n• Is active: " . ($promotion->isActive() ? "Yes" : "No");
        }

        echo "\n✓ Promotions retrieved successfully";

        $this->assertInstanceOf(\Nava\Dinlr\Models\PromotionCollection::class, $promotions);
    }

    /**
     * Test getting a single promotion
     */
    public function testGetSinglePromotion()
    {
        echo "\n\nSTEP 4: Testing get single promotion";
        echo "\n--------------------------------------------------------------";

        $client = new Client($this->testConfig);

        // First get all promotions
        $locationId = null;
        $locations  = $client->locations()->list();
        if (count($locations) > 0) {
            $locationId = $locations->first()->getId();
        }

        $promotions = $client->promotions()->list($locationId);

        if (count($promotions) > 0) {
            $promotionId = $promotions->first()->getId();
            echo "\n• Testing with promotion ID: " . $promotionId;

            $promotion = $client->promotions()->get($promotionId);

            echo "\n• Promotion name: " . $promotion->getName();
            echo "\n• Type: " . $promotion->getType();
            echo "\n• Is auto promotion: " . ($promotion->isAutoPromotion() ? "Yes" : "No");
            echo "\n• Is voucher promotion: " . ($promotion->isVoucherPromotion() ? "Yes" : "No");
            echo "\n• Has no end date: " . ($promotion->hasNoEndDate() ? "Yes" : "No");
            echo "\n✓ Single promotion retrieved successfully";

            $this->assertInstanceOf(\Nava\Dinlr\Models\Promotion::class, $promotion);
            $this->assertEquals($promotionId, $promotion->getId());
        } else {
            $this->markTestSkipped('No promotions available for testing');
        }
    }

    /**
     * Test promotions with query parameters
     */
    public function testPromotionsWithQueryParams()
    {
        echo "\n\nSTEP 5: Testing promotions with query parameters";
        echo "\n--------------------------------------------------------------";

        $client = new Client($this->testConfig);

        $locationId = null;
        $locations  = $client->locations()->list();
        if (count($locations) > 0) {
            $locationId = $locations->first()->getId();
        }

        // Test with pagination
        $params = [
            'limit' => 5,
            'page'  => 1,
        ];

        echo "\n• Testing with limit: " . $params['limit'];
        echo "\n• Testing with page: " . $params['page'];

        $promotions = $client->promotions()->list($locationId, null, $params);

        echo "\n• Promotions returned: " . count($promotions) . " (max " . $params['limit'] . ")";
        echo "\n✓ Query parameters working correctly";

        $this->assertLessThanOrEqual($params['limit'], count($promotions));
    }
}
