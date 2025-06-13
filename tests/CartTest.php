<?php
namespace Nava\Dinlr\Tests;

use Nava\Dinlr\Client;
use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    protected $testConfig;
    protected $client;
    protected static $locationId;

    protected function setUp(): void
    {
        parent::setUp();

        echo "\n\n✅ TEST CASE: Cart & Order Placement API";
        echo "\n==============================================================";
        echo "\nSetting up test configuration...";

        $this->testConfig = require __DIR__ . '/config.php';
        $this->client     = new Client($this->testConfig);

        // Retrieve location ID once and reuse it, to avoid API rate limits
        if (self::$locationId === null) {
            $locations = $this->client->locations()->list();

            if (count($locations) > 0) {
                self::$locationId = $locations->first()->getId();
                echo "\n• Using location ID: " . self::$locationId;
            } else {
                $this->markTestSkipped("No locations found for testing.");
            }
        }

        echo "\n• API URL: " . $this->testConfig['api_url'];
        echo "\n• Restaurant ID: " . $this->testConfig['restaurant_id'];
        echo "\n--------------------------------------------------------------";
    }

    public function testCartCalculation()
    {
        echo "\n\nSTEP 1: Testing cart calculation";
        echo "\n--------------------------------------------------------------";

        try {
            $locationId = self::$locationId;
            $items      = $this->client->items()->list($locationId);

            if (count($items) === 0) {
                $this->markTestSkipped('No items available');
                return;
            }

            $item     = $items->first();
            $variants = $item->getVariants();

            if (empty($variants)) {
                $this->markTestSkipped('No item variants available');
                return;
            }

            $cartData = [
                'location' => $locationId,
                'items'    => [
                    [
                        'item'    => $item->getId(),
                        'qty'     => 2,
                        'variant' => $variants[0]['id'],
                    ],
                ],
            ];

            echo "\n• Calculating cart with 2x " . $item->getName();

            $summary = $this->client->cart()->calculate($cartData);

            echo "\n• Subtotal: " . $summary->getSubtotal();
            echo "\n• Total: " . $summary->getTotal();
            echo "\n• Financial status: " . $summary->getFinancialStatus();
            echo "\n• Items in cart: " . count($summary->getItems());
            echo "\n✓ Cart calculation successful";

            $this->assertInstanceOf(\Nava\Dinlr\Models\CartSummary::class, $summary);
            $this->assertGreaterThan(0, $summary->getTotal());

        } catch (ApiException $e) {
            if ($e->getCode() === 404) {
                $this->markTestSkipped('Cart API not available: ' . $e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    public function testCartWithDiscounts()
    {
        echo "\n\nSTEP 2: Testing cart with discounts";
        echo "\n--------------------------------------------------------------";

        try {
            $locationId = self::$locationId;
            $items     = $this->client->items()->list($locationId);
            $discounts = $this->client->discounts()->list($locationId);

            if (count($items) === 0 || count($discounts) === 0) {
                $this->markTestSkipped('No items or discounts available');
                return;
            }

            $item     = $items->first();
            $variants = $item->getVariants();
            $discount = $discounts->first();

            $cartData = [
                'location'  => $locationId,
                'items'     => [
                    [
                        'item'    => $item->getId(),
                        'qty'     => 1,
                        'variant' => $variants[0]['id'],
                    ],
                ],
                'discounts' => [
                    [
                        'discount' => $discount->getId(),
                    ],
                ],
            ];

            echo "\n• Adding discount: " . $discount->getName();

            $summary = $this->client->cart()->calculate($cartData);

            echo "\n• Total discount: " . $summary->getTotalDiscount();
            echo "\n• Discounts applied: " . count($summary->getDiscounts());
            echo "\n✓ Cart with discounts calculated successfully";

            $this->assertGreaterThan(0, count($summary->getDiscounts()));

        } catch (ApiException $e) {
            $this->markTestSkipped('Cart with discounts not available: ' . $e->getMessage());
        }
    }

    public function testPlaceOrder()
    {
        echo "\n\nSTEP 3: Testing place order";
        echo "\n--------------------------------------------------------------";

        try {
            $locationId = self::$locationId;

            $items = $this->client->items()->list($locationId);
            if (count($items) === 0) {
                $this->markTestSkipped('No items available');
                return;
            }

            $item     = $items->first();
            $variants = $item->getVariants();

            $cartData = [
                'location'   => $locationId,
                'items'      => [
                    [
                        'item'    => $item->getId(),
                        'qty'     => 1,
                        'variant' => $variants[0]['id'],
                    ],
                ],
                'order_info' => [
                    'order_no' => 'TEST' . time(),
                    'notes'    => 'Test order from API test',
                ],
            ];

            echo "\n• Placing test order";

            $order = $this->client->cart()->submit($cartData);

            echo "\n• Order created successfully";
            echo "\n• Order ID: " . $order->getId();
            echo "\n• Order number: " . $order->getOrderNumber();
            echo "\n• Total: " . $order->getTotal();
            echo "\n• Status: " . $order->getStatus();
            echo "\n✓ Order placement successful";

            $this->assertInstanceOf(\Nava\Dinlr\Models\Order::class, $order);
            $this->assertNotEmpty($order->getId());

        } catch (ApiException $e) {
            if ($e->getCode() === 404) {
                $this->markTestSkipped('Order placement not available: ' . $e->getMessage());
            } else {
                $this->fail('Order placement failed: ' . $e->getMessage());
            }
        }
    }

    public function testValidationErrors()
    {
        echo "\n\nSTEP 4: Testing validation errors";
        echo "\n--------------------------------------------------------------";

        echo "\n• Testing missing location";
        try {
            $this->client->cart()->calculate(['items' => []]);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            echo "\n✓ ValidationException caught for missing location";
            $this->assertStringContainsString('location', $e->getMessage());
        }

        echo "\n• Testing empty items";
        try {
            $this->client->cart()->calculate(['location' => 'test', 'items' => []]);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            echo "\n✓ ValidationException caught for empty items";
            $this->assertStringContainsString('Items must be a non-empty array', $e->getMessage());
        }

        echo "\n✓ Validation errors handled correctly";
    }
}
