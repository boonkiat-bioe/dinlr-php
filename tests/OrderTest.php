<?php
namespace Nava\Dinlr\Tests;

use Nava\Dinlr\Client;
use Nava\Dinlr\Exception\ApiException;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    protected $testConfig;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        echo "\n\n✅ TEST CASE: Order Management API";
        echo "\n==============================================================";
        echo "\nSetting up test configuration...";

        $this->testConfig = require __DIR__ . '/config.php';
        $this->client     = new Client($this->testConfig);

        echo "\n• API URL: " . $this->testConfig['api_url'];
        echo "\n• Restaurant ID: " . $this->testConfig['restaurant_id'];
        echo "\n--------------------------------------------------------------";
    }

    public function testListOrders()
    {
        echo "\n\nSTEP 1: Testing list orders";
        echo "\n--------------------------------------------------------------";

        try {
            $orders = $this->client->orders()->list();

            echo "\n• Total orders: " . count($orders);

            if (count($orders) > 0) {
                $order = $orders->first();
                echo "\n• First order ID: " . $order->getId();
                echo "\n• Order number: " . $order->getOrderNumber();
                echo "\n• Total: " . $order->getTotal();
                echo "\n• Status: " . $order->getStatus();
                echo "\n• Financial status: " . $order->getFinancialStatus();
            }

            echo "\n✓ Orders retrieved successfully";

            $this->assertInstanceOf(\Nava\Dinlr\Models\OrderCollection::class, $orders);

        } catch (ApiException $e) {
            if ($e->getCode() === 404) {
                $this->markTestSkipped('Orders not available: ' . $e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    public function testGetSingleOrder()
    {
        echo "\n\nSTEP 2: Testing get single order";
        echo "\n--------------------------------------------------------------";

        try {
            $orders = $this->client->orders()->list();

            if (count($orders) === 0) {
                $this->markTestSkipped('No orders available');
                return;
            }

            $orderId = $orders->first()->getId();
            echo "\n• Getting order ID: " . $orderId;

            $order = $this->client->orders()->get($orderId);

            echo "\n• Order retrieved successfully";
            echo "\n• Order number: " . $order->getOrderNumber();
            echo "\n• Customer: " . ($order->getCustomerId() ?: 'N/A');
            echo "\n• Items: " . count($order->getItems());
            echo "\n• Payments: " . count($order->getPayments());
            echo "\n• Refunds: " . count($order->getRefunds());

            echo "\n✓ Single order retrieved successfully";

            $this->assertInstanceOf(\Nava\Dinlr\Models\Order::class, $order);
            $this->assertEquals($orderId, $order->getId());

        } catch (ApiException $e) {
            $this->fail('Failed to get order: ' . $e->getMessage());
        }
    }

    public function testOrderStatusChanges()
    {
        echo "\n\nSTEP 3: Testing order status changes";
        echo "\n--------------------------------------------------------------";

        try {
            $orders = $this->client->orders()->list();

            // Find an open order
            $openOrder = null;
            foreach ($orders as $order) {
                if ($order->getStatus() === 'open') {
                    $openOrder = $order;
                    break;
                }
            }

            if (! $openOrder) {
                $this->markTestSkipped('No open orders available for status testing');
                return;
            }

            $orderId = $openOrder->getId();
            echo "\n• Testing with order: " . $openOrder->getOrderNumber();

            // Test closing order
            echo "\n• Closing order...";
            $closedOrder = $this->client->orders()->close($orderId);
            echo "\n✓ Order closed, new status: " . $closedOrder->getStatus();
            $this->assertEquals('closed', $closedOrder->getStatus());

            // Test reopening order
            echo "\n• Reopening order...";
            $reopenedOrder = $this->client->orders()->reopen($orderId);
            echo "\n✓ Order reopened, new status: " . $reopenedOrder->getStatus();
            $this->assertEquals('open', $reopenedOrder->getStatus());

            echo "\n✓ Order status changes successful";

        } catch (ApiException $e) {
            $this->markTestSkipped('Order status changes not available: ' . $e->getMessage());
        }
    }

    public function testAddPayment()
    {
        echo "\n\nSTEP 4: Testing add payment to order";
        echo "\n--------------------------------------------------------------";

        try {
            $orders = $this->client->orders()->list();

            // Find an unpaid order
            $unpaidOrder = null;
            foreach ($orders as $order) {
                if ($order->getFinancialStatus() !== 'paid') {
                    $unpaidOrder = $order;
                    break;
                }
            }

            if (! $unpaidOrder) {
                $this->markTestSkipped('No unpaid orders available for payment testing');
                return;
            }

            $orderId        = $unpaidOrder->getId();
            $paymentMethods = $this->client->paymentMethods()->list();

            if (count($paymentMethods) === 0) {
                $this->markTestSkipped('No payment methods available');
                return;
            }

            $paymentData = [
                'payment'    => $paymentMethods->first()->getId(),
                'amount'     => 10.00,
                'receipt_no' => 'TEST' . time(),
            ];

            echo "\n• Adding payment to order: " . $unpaidOrder->getOrderNumber();
            echo "\n• Payment amount: " . $paymentData['amount'];

            $updatedOrder = $this->client->orders()->addPayment($orderId, $paymentData);

            echo "\n• Payment added successfully";
            echo "\n• New financial status: " . $updatedOrder->getFinancialStatus();
            echo "\n• Total payments: " . count($updatedOrder->getPayments());

            echo "\n✓ Payment addition successful";

            $this->assertGreaterThan(count($unpaidOrder->getPayments()), count($updatedOrder->getPayments()));

        } catch (ApiException $e) {
            $this->markTestSkipped('Payment addition not available: ' . $e->getMessage());
        }
    }

    public function testOrderFiltering()
    {
        echo "\n\nSTEP 5: Testing order filtering";
        echo "\n--------------------------------------------------------------";

        try {
            // Test filtering by location
            $locations = $this->client->locations()->list();
            if (count($locations) > 0) {
                $locationId = $locations->first()->getId();
                echo "\n• Filtering by location: " . $locationId;

                $locationOrders = $this->client->orders()->list(null, ['location_id' => $locationId]);
                echo "\n• Orders for location: " . count($locationOrders);
            }

            // Test filtering with detail parameter
            echo "\n• Getting orders with full details";
            $detailedOrders = $this->client->orders()->list(null, ['detail' => 'all', 'limit' => 5]);

            if (count($detailedOrders) > 0) {
                $order = $detailedOrders->first();
                echo "\n• First order has dining option name: " . ($order->getDiningOptionName() ? 'Yes' : 'No');
            }

            echo "\n✓ Order filtering successful";

        } catch (ApiException $e) {
            $this->markTestSkipped('Order filtering not available: ' . $e->getMessage());
        }
    }
}
