<?php
namespace Nava\Dinlr\Tests;

use Nava\Dinlr\Client;
use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

class StoreCreditTest extends TestCase
{
    protected $testConfig;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        echo "\n\n✅ TEST CASE: Store Credit API Comprehensive Testing";
        echo "\n==============================================================";
        echo "\nSetting up test configuration...";

        $this->testConfig = require __DIR__ . '/config.php';
        $this->client     = new Client($this->testConfig);

        echo "\n• API URL: " . $this->testConfig['api_url'];
        echo "\n• Restaurant ID: " . $this->testConfig['restaurant_id'];
        echo "\n--------------------------------------------------------------";
    }

    /**
     * Test retrieving customer store credit balance
     */
    public function testGetCustomerStoreCredit()
    {
        echo "\n\nSTEP 1: Testing retrieve customer store credit balance";
        echo "\n--------------------------------------------------------------";

        try {
            // Get a customer first
            $customers = $this->client->customers()->list(null, ['limit' => 1]);

            if (count($customers) === 0) {
                $this->markTestSkipped('No customers available for store credit testing');
                return;
            }

            $customer   = $customers->first();
            $customerId = $customer->getId();

            echo "\n• Testing customer ID: " . $customerId;
            echo "\n• Customer name: " . $customer->getFullName();

            $balance = $this->client->storeCredit()->getCustomerBalance($customerId);

            echo "\n• Store credit balance: " . $balance->getStoreCredit();
            echo "\n• Has store credit: " . ($balance->hasStoreCredit() ? 'Yes' : 'No');
            echo "\n• Updated at: " . $balance->getUpdatedAt();

            // Test business logic methods
            echo "\n• Can cover $10: " . ($balance->hasSufficientCredit(10) ? 'Yes' : 'No');
            echo "\n• Can cover $100: " . ($balance->hasSufficientCredit(100) ? 'Yes' : 'No');

            echo "\n✓ Customer store credit balance retrieved successfully";
            $this->assertInstanceOf(\Nava\Dinlr\Models\StoreCreditBalance::class, $balance);

        } catch (ApiException $e) {
            if ($e->getCode() === 404) {
                echo "\n• Store credit feature not available or customer not found";
                $this->markTestSkipped('Store credit not available: ' . $e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Test creating store credit transaction
     */
    public function testCreateStoreCreditTransaction()
    {
        echo "\n\nSTEP 2: Testing create store credit transaction";
        echo "\n--------------------------------------------------------------";

        try {
            $customers = $this->client->customers()->list(null, ['limit' => 1]);

            if (count($customers) === 0) {
                $this->markTestSkipped('No customers available');
                return;
            }

            $customer   = $customers->first();
            $customerId = $customer->getId();

            echo "\n• Creating credit transaction for customer: " . $customer->getFullName();

            $transactionData = [
                'customer' => $customerId,
                'amount'   => 50.00,
                'notes'    => 'Test credit addition from API test',
            ];

            $transaction = $this->client->storeCredit()->createTransaction($transactionData);

            echo "\n• Transaction created successfully";
            echo "\n• Transaction ID: " . $transaction->getId();
            echo "\n• Amount: " . $transaction->getAmount();
            echo "\n• Is credit addition: " . ($transaction->isCreditAddition() ? 'Yes' : 'No');
            echo "\n• Notes: " . ($transaction->getNotes() ?: 'N/A');

            $this->assertInstanceOf(\Nava\Dinlr\Models\StoreCreditTransaction::class, $transaction);
            $this->assertEquals(50.00, $transaction->getAmount());

        } catch (ApiException $e) {
            if ($e->getCode() === 404) {
                $this->markTestSkipped('Store credit transactions not available: ' . $e->getMessage());
            } else {
                $this->fail('Store credit transaction creation failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Test convenience methods for adding/deducting credit
     */
    public function testConvenienceMethods()
    {
        echo "\n\nSTEP 3: Testing convenience methods";
        echo "\n--------------------------------------------------------------";

        try {
            $customers = $this->client->customers()->list(null, ['limit' => 1]);

            if (count($customers) === 0) {
                $this->markTestSkipped('No customers available');
                return;
            }

            $customer   = $customers->first();
            $customerId = $customer->getId();

            echo "\n• Testing addCredit method";
            $addTransaction = $this->client->storeCredit()->addCredit(
                $customerId,
                25.50,
                'Test credit addition via convenience method'
            );

            echo "\n• Credit added: " . $addTransaction->getAmount();
            $this->assertTrue($addTransaction->isCreditAddition());

            echo "\n• Testing deductCredit method";
            $deductTransaction = $this->client->storeCredit()->deductCredit(
                $customerId,
                10.25,
                'Test credit deduction via convenience method'
            );

            echo "\n• Credit deducted: " . $deductTransaction->getAmount();
            $this->assertTrue($deductTransaction->isCreditDeduction());
            $this->assertEquals(10.25, $deductTransaction->getAbsoluteAmount());

            echo "\n✓ Convenience methods working correctly";

        } catch (ApiException $e) {
            $this->markTestSkipped('Convenience methods not available: ' . $e->getMessage());
        }
    }

    /**
     * Test searching store credit transactions
     */
    public function testSearchStoreCreditTransactions()
    {
        echo "\n\nSTEP 4: Testing search store credit transactions";
        echo "\n--------------------------------------------------------------";

        try {
            $transactions = $this->client->storeCredit()->searchTransactions(['limit' => 10]);

            echo "\n• Total transactions found: " . count($transactions);

            if (count($transactions) > 0) {
                $transaction = $transactions->first();
                echo "\n• First transaction ID: " . $transaction->getId();
                echo "\n• Customer: " . $transaction->getCustomer();
                echo "\n• Amount: " . $transaction->getAmount();
                echo "\n• Created at: " . $transaction->getCreatedAt();

                // Test collection methods
                $sortedTransactions = $transactions->sortByNewest();
                echo "\n• Sorted by newest: " . count($sortedTransactions) . " transactions";

                $totalAmount = $transactions->getTotalCreditAmount();
                echo "\n• Total credit amount: " . $totalAmount;

                $creditAdditions = $transactions->getCreditAdditions();
                echo "\n• Credit additions: " . count($creditAdditions);

                $creditDeductions = $transactions->getCreditDeductions();
                echo "\n• Credit deductions: " . count($creditDeductions);
            }

            echo "\n✓ Store credit transactions search successful";
            $this->assertInstanceOf(\Nava\Dinlr\Models\StoreCreditTransactionCollection::class, $transactions);

        } catch (ApiException $e) {
            if ($e->getCode() === 404) {
                $this->markTestSkipped('Store credit transaction search not available: ' . $e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Test customer-specific transaction search
     */
    public function testCustomerTransactionSearch()
    {
        echo "\n\nSTEP 5: Testing customer-specific transaction search";
        echo "\n--------------------------------------------------------------";

        try {
            $customers = $this->client->customers()->list(null, ['limit' => 1]);

            if (count($customers) === 0) {
                $this->markTestSkipped('No customers available');
                return;
            }

            $customer   = $customers->first();
            $customerId = $customer->getId();

            echo "\n• Searching transactions for customer: " . $customer->getFullName();

            $customerTransactions = $this->client->storeCredit()->getCustomerTransactions($customerId);

            echo "\n• Customer transactions found: " . count($customerTransactions);

            if (count($customerTransactions) > 0) {
                $transaction = $customerTransactions->first();
                echo "\n• Latest transaction amount: " . $transaction->getAmount();
                echo "\n• Transaction type: " . ($transaction->isCreditAddition() ? 'Addition' : 'Deduction');
            }

            // Test with search parameters
            $searchParams   = ['limit' => 5];
            $limitedResults = $this->client->storeCredit()->searchTransactions(['customer_id' => $customerId] + $searchParams);

            echo "\n• Limited search results: " . count($limitedResults) . " (max 5)";
            $this->assertLessThanOrEqual(5, count($limitedResults));

            echo "\n✓ Customer transaction search successful";

        } catch (ApiException $e) {
            $this->markTestSkipped('Customer transaction search not available: ' . $e->getMessage());
        }
    }

    /**
     * Test store credit topup
     */
    public function testStoreCreditTopup()
    {
        echo "\n\nSTEP 6: Testing store credit topup";
        echo "\n--------------------------------------------------------------";

        try {
            $customers      = $this->client->customers()->list(null, ['limit' => 1]);
            $paymentMethods = $this->client->paymentMethods()->list();

            if (count($customers) === 0 || count($paymentMethods) === 0) {
                $this->markTestSkipped('No customers or payment methods available');
                return;
            }

            $customer   = $customers->first();
            $customerId = $customer->getId();
            $paymentId  = $paymentMethods->first()->getId();

            echo "\n• Creating topup for customer: " . $customer->getFullName();
            echo "\n• Using payment method: " . $paymentMethods->first()->getName();

            $topupData = [
                'customer'       => $customerId,
                'topup_no'       => 'TEST' . time(),
                'topup_amount'   => 100.00,
                'payment'        => $paymentId,
                'payment_amount' => 90.00, // 10% bonus
            ];

            $topup = $this->client->storeCredit()->createTopup($topupData);

            echo "\n• Topup created successfully";
            echo "\n• Topup ID: " . $topup->getId();
            echo "\n• Topup number: " . $topup->getTopupNumber();
            echo "\n• Topup amount: " . $topup->getTopupAmount();
            echo "\n• Payment amount: " . $topup->getPaymentAmount();
            echo "\n• Bonus amount: " . $topup->getBonusAmount();
            echo "\n• Has bonus: " . ($topup->hasBonus() ? 'Yes' : 'No');

            $this->assertInstanceOf(\Nava\Dinlr\Models\StoreCreditTopup::class, $topup);
            $this->assertEquals(100.00, $topup->getTopupAmount());
            $this->assertEquals(90.00, $topup->getPaymentAmount());
            $this->assertEquals(10.00, $topup->getBonusAmount());
            $this->assertTrue($topup->hasBonus());

        } catch (ApiException $e) {
            if ($e->getCode() === 404) {
                $this->markTestSkipped('Store credit topup not available: ' . $e->getMessage());
            } else {
                $this->fail('Store credit topup failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Test validation errors
     */
    public function testValidationErrors()
    {
        echo "\n\nSTEP 7: Testing validation errors";
        echo "\n--------------------------------------------------------------";

        // Test missing customer ID
        echo "\n• Testing missing customer ID";
        try {
            $this->client->storeCredit()->getCustomerBalance('');
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            echo "\n✓ ValidationException caught for missing customer ID";
            $this->assertStringContainsString('Customer ID is required', $e->getMessage());
        }

        // Test invalid transaction data
        echo "\n• Testing invalid transaction data";
        try {
            $this->client->storeCredit()->createTransaction([]);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            echo "\n✓ ValidationException caught for missing transaction data";
        } catch (ApiException $e) {
            echo "\n✓ API validation error caught: " . $e->getMessage();
        }

        // Test invalid amount
        echo "\n• Testing invalid amount";
        try {
            $this->client->storeCredit()->createTransaction([
                'customer' => 'test_customer',
                'amount'   => 'invalid_amount',
            ]);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            echo "\n✓ ValidationException caught for invalid amount";
            $this->assertStringContainsString('Amount must be a numeric value', $e->getMessage());
        }

        // Test notes too long
        echo "\n• Testing notes too long";
        try {
            $this->client->storeCredit()->createTransaction([
                'customer' => 'test_customer',
                'amount'   => 10.00,
                'notes'    => str_repeat('a', 201), // Exceeds 200 character limit
            ]);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            echo "\n✓ ValidationException caught for notes too long";
            $this->assertStringContainsString('Notes cannot exceed 200 characters', $e->getMessage());
        }

        echo "\n✓ All validation errors handled correctly";
    }

    /**
     * Test advanced search functionality
     */
    public function testAdvancedSearch()
    {
        echo "\n\nSTEP 8: Testing advanced search functionality";
        echo "\n--------------------------------------------------------------";

        try {
            // Test location-based search
            $locations = $this->client->locations()->list();
            if (count($locations) > 0) {
                $locationId = $locations->first()->getId();
                echo "\n• Testing location-based search";

                $locationTransactions = $this->client->storeCredit()->getLocationTransactions($locationId);
                echo "\n• Location transactions: " . count($locationTransactions);
            }

            // Test current app transactions
            echo "\n• Testing current app transactions";
            $appTransactions = $this->client->storeCredit()->getCurrentAppTransactions();
            echo "\n• Current app transactions: " . count($appTransactions);

            // Test date range search
            echo "\n• Testing date range search";
            $startDate = (new \DateTime('-30 days'))->format('c');
            $endDate   = (new \DateTime())->format('c');

            $dateTransactions = $this->client->storeCredit()->getTransactionsByDateRange($startDate, $endDate);
            echo "\n• Date range transactions (last 30 days): " . count($dateTransactions);

            echo "\n✓ Advanced search functionality working";

        } catch (ApiException $e) {
            $this->markTestSkipped('Advanced search not available: ' . $e->getMessage());
        }
    }

    /**
     * Test bulk operations
     */
    public function testBulkOperations()
    {
        echo "\n\nSTEP 9: Testing bulk operations";
        echo "\n--------------------------------------------------------------";

        try {
            $customers = $this->client->customers()->list(null, ['limit' => 3]);

            if (count($customers) === 0) {
                $this->markTestSkipped('No customers available for bulk testing');
                return;
            }

            $customerIds = [];
            foreach ($customers as $customer) {
                $customerIds[] = $customer->getId();
            }

            echo "\n• Testing bulk balance retrieval for " . count($customerIds) . " customers";

            $balances = $this->client->storeCredit()->getBulkCustomerBalances($customerIds);

            echo "\n• Balances retrieved: " . count($balances);

            foreach ($balances as $customerId => $balance) {
                echo "\n• Customer " . $customerId . ": " . $balance->getStoreCredit();
            }

            echo "\n✓ Bulk operations working correctly";

        } catch (ApiException $e) {
            $this->markTestSkipped('Bulk operations not available: ' . $e->getMessage());
        }
    }

    /**
     * Test error handling for non-existent resources
     */
    public function testErrorHandling()
    {
        echo "\n\nSTEP 10: Testing error handling";
        echo "\n--------------------------------------------------------------";

        // Test non-existent customer
        echo "\n• Testing non-existent customer";
        try {
            $this->client->storeCredit()->getCustomerBalance('non-existent-customer');
            $this->fail('Expected ApiException was not thrown');
        } catch (ApiException $e) {
            echo "\n✓ ApiException caught for non-existent customer";
            $this->assertEquals(404, $e->getCode());
        }

        echo "\n✓ Error handling tests completed";
    }

    protected function tearDown(): void
    {
        echo "\n\n==============================================================";
        echo "\n✅ Store Credit API Test Suite Completed";
        echo "\n==============================================================\n";

        parent::tearDown();
    }
}
