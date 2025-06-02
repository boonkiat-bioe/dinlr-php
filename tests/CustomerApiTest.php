<?php
namespace Nava\Dinlr\Tests;

use Nava\Dinlr\Client;
use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

class CustomerApiTest extends TestCase
{
    protected $testConfig;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        echo "\n\n✅ TEST CASE: Customers API Comprehensive Testing";
        echo "\n==============================================================";
        echo "\nSetting up test configuration...";

        $this->testConfig = require __DIR__ . '/config.php';
        $this->client     = new Client($this->testConfig);

        echo "\n• API URL: " . $this->testConfig['api_url'];
        echo "\n• Restaurant ID: " . $this->testConfig['restaurant_id'];
        echo "\n--------------------------------------------------------------";
    }

    /**
     * Test retrieving all customers
     */
    public function testGetAllCustomers()
    {
        echo "\n\nSTEP 1: Testing retrieve all customers";
        echo "\n--------------------------------------------------------------";

        try {
            $customers = $this->client->customers()->list(); // Fixed: no parameters

            echo "\n• Total customers: " . count($customers);

            if (count($customers) > 0) {
                $customer = $customers->first();
                echo "\n• First customer ID: " . $customer->getId();
                echo "\n• First customer name: " . $customer->getFullName();
                echo "\n• First customer email: " . ($customer->getEmail() ?: 'N/A');
                echo "\n• First customer phone: " . ($customer->getPhone() ?: 'N/A');
            }

            echo "\n✓ Customers retrieved successfully";
            $this->assertInstanceOf(\Nava\Dinlr\Models\CustomerCollection::class, $customers);

        } catch (ApiException $e) {
            if ($e->getCode() === 404) {
                echo "\n• No customers endpoint available or no customers found";
                $this->markTestSkipped('Customer endpoint not available: ' . $e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Test creating a new customer
     */
    public function testCreateCustomer()
    {
        echo "\n\nSTEP 2: Testing create new customer";
        echo "\n--------------------------------------------------------------";

        $timestamp    = time();
        $customerData = [
            'reference'               => 'TEST_REF_' . $timestamp,
            'first_name'              => 'John',
            'last_name'               => 'Doe',
            'email'                   => 'john.doe.test.' . $timestamp . '@example.com',
            'phone'                   => '+1234567890',
            'company_name'            => 'Test Company',
            'dob'                     => '1990-01-01',
            'gender'                  => 'M',
            'address1'                => '123 Test Street',
            'address2'                => 'Apt 4B',
            'city'                    => 'Test City',
            'country'                 => 'US',
            'postal'                  => '12345',
            'notes'                   => 'Test customer created by API test',
            'marketing_consent_email' => true,
            'marketing_consent_text'  => false,
            'marketing_consent_phone' => false,
        ];

        echo "\n• Creating customer with email: " . $customerData['email'];

        try {
            $newCustomer = $this->client->customers()->create($customerData);

            echo "\n• Customer created successfully";
            echo "\n• Customer ID: " . $newCustomer->getId();
            echo "\n• Customer name: " . $newCustomer->getFullName();
            echo "\n• Customer email: " . $newCustomer->getEmail();

            echo "\n✓ Customer creation successful";

            $this->assertInstanceOf(\Nava\Dinlr\Models\Customer::class, $newCustomer);
            $this->assertEquals($customerData['email'], $newCustomer->getEmail());
            $this->assertEquals($customerData['first_name'], $newCustomer->getFirstName());
            $this->assertEquals($customerData['last_name'], $newCustomer->getLastName());

            return $newCustomer;

        } catch (ApiException $e) {
            echo "\n• API Error: " . $e->getMessage();
            if ($e->getCode() === 404) {
                $this->markTestSkipped('Customer creation not available: ' . $e->getMessage());
            } else {
                $this->fail('Customer creation failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Test updating a customer
     */
    public function testUpdateCustomer()
    {
        echo "\n\nSTEP 3: Testing update customer";
        echo "\n--------------------------------------------------------------";

        try {
            // Fixed: correct parameter order (restaurantId, params)
            $customers = $this->client->customers()->list(null, ['limit' => 1]);

            if (count($customers) === 0) {
                $this->markTestSkipped('No customers available for update test');
                return;
            }

            $customer   = $customers->first();
            $customerId = $customer->getId();

            echo "\n• Updating customer ID: " . $customerId;

            $updateData = [
                'notes'                   => 'Updated by API test on ' . date('Y-m-d H:i:s'),
                'marketing_consent_email' => false,
            ];

            $updatedCustomer = $this->client->customers()->update($customerId, $updateData);

            echo "\n• Customer updated successfully";
            echo "\n• New notes: " . $updatedCustomer->getAttribute('notes');

            echo "\n✓ Customer update successful";

            $this->assertInstanceOf(\Nava\Dinlr\Models\Customer::class, $updatedCustomer);
            $this->assertEquals($customerId, $updatedCustomer->getId());

        } catch (ApiException $e) {
            echo "\n• API Error: " . $e->getMessage();
            if ($e->getCode() === 404) {
                $this->markTestSkipped('Customer update not available: ' . $e->getMessage());
            } else {
                $this->fail('Customer update failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Test getting a single customer
     */
    public function testGetSingleCustomer()
    {
        echo "\n\nSTEP 4: Testing get single customer";
        echo "\n--------------------------------------------------------------";

        try {
            // Fixed: correct parameter order
            $customers = $this->client->customers()->list(null, ['limit' => 1]);

            if (count($customers) === 0) {
                $this->markTestSkipped('No customers available for single customer test');
                return;
            }

            $customerId = $customers->first()->getId();
            echo "\n• Retrieving customer ID: " . $customerId;

            $customer = $this->client->customers()->get($customerId);

            echo "\n• Customer retrieved successfully";
            echo "\n• Customer name: " . $customer->getFullName();
            echo "\n• Customer reference: " . ($customer->getReference() ?: 'N/A');

            echo "\n✓ Single customer retrieval successful";

            $this->assertInstanceOf(\Nava\Dinlr\Models\Customer::class, $customer);
            $this->assertEquals($customerId, $customer->getId());

        } catch (ApiException $e) {
            echo "\n• API Error: " . $e->getMessage();
            if ($e->getCode() === 404) {
                $this->markTestSkipped('Customer retrieval not available: ' . $e->getMessage());
            } else {
                $this->fail('Single customer retrieval failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Test searching customers by email
     */
    public function testSearchCustomersByEmail()
    {
        echo "\n\nSTEP 5: Testing search customers by email";
        echo "\n--------------------------------------------------------------";

        try {
            // Fixed: correct parameter order
            $customers = $this->client->customers()->list(null, ['limit' => 10]);

            // Find a customer with an email
            $testEmail = null;
            foreach ($customers as $customer) {
                if ($customer->getEmail()) {
                    $testEmail = $customer->getEmail();
                    break;
                }
            }

            if (! $testEmail) {
                $this->markTestSkipped('No customers with email found for search test');
                return;
            }

            echo "\n• Searching for email: " . $testEmail;

            $searchResults = $this->client->customers()->search(['email' => $testEmail]);

            echo "\n• Search completed successfully";
            echo "\n• Results found: " . count($searchResults);

            if (count($searchResults) > 0) {
                echo "\n• First result email: " . $searchResults->first()->getEmail();
            }

            echo "\n✓ Customer search by email successful";

            $this->assertInstanceOf(\Nava\Dinlr\Models\CustomerCollection::class, $searchResults);

        } catch (ApiException $e) {
            echo "\n• API Error: " . $e->getMessage();
            if ($e->getCode() === 404) {
                $this->markTestSkipped('Customer search not available: ' . $e->getMessage());
            } else {
                $this->fail('Customer search by email failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Test searching customers by phone
     */
    public function testSearchCustomersByPhone()
    {
        echo "\n\nSTEP 6: Testing search customers by phone";
        echo "\n--------------------------------------------------------------";

        try {
            // Fixed: correct parameter order
            $customers = $this->client->customers()->list(null, ['limit' => 10]);

            // Find a customer with a phone
            $testPhone = null;
            foreach ($customers as $customer) {
                if ($customer->getPhone()) {
                    $testPhone = $customer->getPhone();
                    break;
                }
            }

            if (! $testPhone) {
                $this->markTestSkipped('No customers with phone found for search test');
                return;
            }

            echo "\n• Searching for phone: " . $testPhone;

            $searchResults = $this->client->customers()->search(['phone' => $testPhone]);

            echo "\n• Search completed successfully";
            echo "\n• Results found: " . count($searchResults);

            echo "\n✓ Customer search by phone successful";

            $this->assertInstanceOf(\Nava\Dinlr\Models\CustomerCollection::class, $searchResults);

        } catch (ApiException $e) {
            echo "\n• API Error: " . $e->getMessage();
            if ($e->getCode() === 404) {
                $this->markTestSkipped('Customer search not available: ' . $e->getMessage());
            } else {
                $this->fail('Customer search by phone failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Test searching customers by reference
     */
    public function testSearchCustomersByReference()
    {
        echo "\n\nSTEP 7: Testing search customers by reference";
        echo "\n--------------------------------------------------------------";

        try {
            // Fixed: correct parameter order
            $customers = $this->client->customers()->list(null, ['limit' => 10]);

            // Find a customer with a reference
            $testReference = null;
            foreach ($customers as $customer) {
                if ($customer->getReference()) {
                    $testReference = $customer->getReference();
                    break;
                }
            }

            if (! $testReference) {
                $this->markTestSkipped('No customers with reference found for search test');
                return;
            }

            echo "\n• Searching for reference: " . $testReference;

            $searchResults = $this->client->customers()->search(['reference' => $testReference]);

            echo "\n• Search completed successfully";
            echo "\n• Results found: " . count($searchResults);

            echo "\n✓ Customer search by reference successful";

            $this->assertInstanceOf(\Nava\Dinlr\Models\CustomerCollection::class, $searchResults);

        } catch (ApiException $e) {
            echo "\n• API Error: " . $e->getMessage();
            if ($e->getCode() === 404) {
                $this->markTestSkipped('Customer search not available: ' . $e->getMessage());
            } else {
                $this->fail('Customer search by reference failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Test getting all customer groups
     */
    public function testGetCustomerGroups()
    {
        echo "\n\nSTEP 8: Testing get all customer groups";
        echo "\n--------------------------------------------------------------";

        try {
            $customerGroups = $this->client->customerGroups()->list();

            echo "\n• Total customer groups: " . count($customerGroups);

            if (count($customerGroups) > 0) {
                $group = $customerGroups->first();
                echo "\n• First group ID: " . $group->getId();
                echo "\n• First group name: " . $group->getName();
            }

            echo "\n✓ Customer groups retrieved successfully";

            $this->assertInstanceOf(\Nava\Dinlr\Models\CustomerGroupCollection::class, $customerGroups);

        } catch (ApiException $e) {
            echo "\n• API Error: " . $e->getMessage();
            if ($e->getCode() === 404) {
                $this->markTestSkipped('Customer groups not available: ' . $e->getMessage());
            } else {
                $this->fail('Customer groups retrieval failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Test pagination
     */
    public function testCustomerPagination()
    {
        echo "\n\nSTEP 9: Testing customer pagination";
        echo "\n--------------------------------------------------------------";

        $params = [
            'limit' => 5,
            'page'  => 1,
        ];

        echo "\n• Testing with limit: " . $params['limit'];
        echo "\n• Testing with page: " . $params['page'];

        try {
            // Fixed: correct parameter order
            $customers = $this->client->customers()->list(null, $params);

            echo "\n• Customers returned: " . count($customers) . " (max " . $params['limit'] . ")";
            echo "\n✓ Pagination working correctly";

            $this->assertLessThanOrEqual($params['limit'], count($customers));

        } catch (ApiException $e) {
            echo "\n• API Error: " . $e->getMessage();
            if ($e->getCode() === 404) {
                $this->markTestSkipped('Customer pagination not available: ' . $e->getMessage());
            } else {
                $this->fail('Customer pagination test failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Test validation errors
     */
    public function testValidationErrors()
    {
        echo "\n\nSTEP 10: Testing validation errors";
        echo "\n--------------------------------------------------------------";

        // Test creating customer without required fields
        echo "\n• Testing customer creation without required fields";

        try {
            $this->client->customers()->create([]);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            echo "\n✓ ValidationException caught for missing required fields";
            $this->assertStringContainsString('required', strtolower($e->getMessage()));
        } catch (ApiException $e) {
            echo "\n✓ API validation error caught: " . $e->getMessage();
            $this->assertGreaterThanOrEqual(400, $e->getCode());
        }

        // Test creating customer with only first_name (should succeed)
        echo "\n• Testing customer creation with minimal data";

        try {
            $timestamp       = time();
            $minimalCustomer = $this->client->customers()->create([
                'first_name' => 'TestUser' . $timestamp,
            ]);

            echo "\n✓ Customer created with minimal data";
            $this->assertInstanceOf(\Nava\Dinlr\Models\Customer::class, $minimalCustomer);

        } catch (ValidationException $e) {
            echo "\n✓ Validation requires at least one of: reference, first_name, last_name";
            $this->assertStringContainsString('required', strtolower($e->getMessage()));
        } catch (ApiException $e) {
            echo "\n• API Error (expected): " . $e->getMessage();
            $this->markTestSkipped('Customer creation not available');
        }

        echo "\n✓ Validation tests completed";
    }

    /**
     * Test customer model methods
     */
    public function testCustomerModelMethods()
    {
        echo "\n\nSTEP 11: Testing customer model methods";
        echo "\n--------------------------------------------------------------";

        try {
            // Fixed: correct parameter order
            $customers = $this->client->customers()->list(null, ['limit' => 1]);

            if (count($customers) === 0) {
                $this->markTestSkipped('No customers available for model method testing');
                return;
            }

            $customer = $customers->first();

            echo "\n• Testing customer model methods";
            echo "\n• Customer ID: " . $customer->getId();
            echo "\n• Full name: '" . $customer->getFullName() . "'";
            echo "\n• Reference: " . ($customer->getReference() ?: 'N/A');
            echo "\n• Email: " . ($customer->getEmail() ?: 'N/A');
            echo "\n• Phone: " . ($customer->getPhone() ?: 'N/A');
            echo "\n• Updated at: " . $customer->getUpdatedAt();

            echo "\n✓ Customer model methods working correctly";

            $this->assertNotEmpty($customer->getId());
            $this->assertIsString($customer->getFullName());

        } catch (ApiException $e) {
            echo "\n• API Error: " . $e->getMessage();
            if ($e->getCode() === 404) {
                $this->markTestSkipped('No customers available for model testing');
            } else {
                throw $e;
            }
        }
    }

    /**
     * Test error handling
     */
    public function testErrorHandling()
    {
        echo "\n\nSTEP 12: Testing error handling";
        echo "\n--------------------------------------------------------------";

        // Test getting non-existent customer
        echo "\n• Testing retrieval of non-existent customer";

        try {
            $this->client->customers()->get('non-existent-customer-id');
            $this->fail('Expected ApiException was not thrown');
        } catch (ApiException $e) {
            echo "\n✓ ApiException caught for non-existent customer";
            $this->assertEquals(404, $e->getCode());
        }

        // Test updating non-existent customer
        echo "\n• Testing update of non-existent customer";

        try {
            $this->client->customers()->update('non-existent-customer-id', ['notes' => 'test']);
            $this->fail('Expected ApiException was not thrown');
        } catch (ApiException $e) {
            echo "\n✓ ApiException caught for non-existent customer update";
            $this->assertEquals(404, $e->getCode());
        }

        echo "\n✓ Error handling tests completed";
    }

    protected function tearDown(): void
    {
        echo "\n\n==============================================================";
        echo "\n✅ Customer API Test Suite Completed";
        echo "\n==============================================================\n";

        parent::tearDown();
    }
}
