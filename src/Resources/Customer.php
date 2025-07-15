<?php
namespace Nava\Dinlr\Resources;

use Nava\Dinlr\Contracts\ResourceInterface;
use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Exception\ValidationException;
use Nava\Dinlr\Models\Customer as CustomerModel;
use Nava\Dinlr\Models\CustomerCollection;

class Customer extends AbstractResource
{
    protected $resourcePath = 'onlineorder/customers';

    /**
     * List all customers
     *
     * @param string|null $restaurantId Restaurant ID (uses config default if null)
     * @param array $params Query parameters (limit, page, updated_at_min, etc.)
     * @return CustomerCollection
     * @throws ApiException
     * @throws ValidationException
     */
    public function list($restaurantId = null, array $params = []): CustomerCollection
    {
        $this->validatePagination($params);

        $path     = $this->buildPath($restaurantId);
        $response = $this->client->request('GET', $path, $params);

        return new CustomerCollection($response['data'] ?? []);
    }

    /**
     * Get a single customer
     *
     * @param string $customerId Customer ID
     * @param string|null $restaurantId Restaurant ID (uses config default if null)
     * @return CustomerModel
     * @throws ApiException
     * @throws ValidationException
     */
    public function get(string $customerId, ?string $restaurantId = null): CustomerModel
    {
        $this->validateString($customerId, 'Customer ID');

        $path     = $this->buildPath($restaurantId, $customerId);
        $response = $this->client->request('GET', $path);

        return new CustomerModel($response['data'] ?? []);
    }

    /**
     * Create a new customer
     *
     * @param array $data Customer data
     * @param string|null $restaurantId Restaurant ID (uses config default if null)
     * @return CustomerModel
     * @throws ApiException
     * @throws ValidationException
     */
    public function create(array $data, ?string $restaurantId = null): CustomerModel
    {
        // Define validation rules
        $rules = [
            'first_name' => ['type' => 'string', 'max_length' => 50, 'required' => true],
            'email'      => ['type' => 'email', 'required' => true],
            'phone'      => ['type' => 'string', 'max_length' => 20],
        ];

        // Validate and sanitize all at once
        $sanitizedData = $this->validateAndSanitizeArray($data, $rules);

        $path     = $this->buildPath($restaurantId);
        $response = $this->client->request('POST', $path, $sanitizedData);

        return new CustomerModel($response['data'] ?? []);
    }

    /**
     * Update a customer
     *
     * @param string $customerId Customer ID
     * @param array $data Customer data
     * @param string|null $restaurantId Restaurant ID (uses config default if null)
     * @return CustomerModel
     * @throws ApiException
     * @throws ValidationException
     */
    public function update(string $customerId, array $data, ?string $restaurantId = null): CustomerModel
    {
        // Ensure customer exists
        $customer = $this->get($customerId);
        $customerData = $customer->toArray();

        // Only update fields that are provided in $data
        // This allows partial updates without overwriting existing data
        foreach ($customerData as $field => $value) {
            if (array_key_exists($field, $data)) {
                $customerData[$field] = $data[$field];
            }
        }

        // echo "\nUpdating Customer Data: " . print_r($customerData, true);

        $this->validateString($customerId, 'Customer ID');
        $this->validateCustomerData($customerData, false);

        $path     = $this->buildPath($restaurantId, $customerId);
        $response = $this->client->request('PUT', $path, $customerData instanceof CustomerModel ? $customerData->toArray() : $customerData);

        return new CustomerModel($response['data'] ?? []);
    }

    /**
     * Search for customers
     *
     * @param array $params Search parameters (email, phone, reference)
     * @param string|null $restaurantId Restaurant ID (uses config default if null)
     * @return CustomerCollection
     * @throws ApiException
     * @throws ValidationException
     */
    public function search(array $params, ?string $restaurantId = null): CustomerCollection
    {
        $this->validateSearchParams($params);

        $path     = $this->buildPath($restaurantId, 'search');
        $response = $this->client->request('GET', $path, $params);

        return new CustomerCollection($response['data'] ?? []);
    }

    /**
     * Validate customer data
     */
    private function validateCustomerData(array $data, bool $isCreate): void
    {
        // For create, require at least one identifier
        if ($isCreate) {
            if (empty($data['reference']) && empty($data['first_name']) && empty($data['last_name'])) {
                throw new ValidationException(
                    'At least one of the following fields is required: reference, first_name, last_name',
                    ['required_one_of' => ['reference', 'first_name', 'last_name']]
                );
            }
        }

        // Validate field lengths
        $stringFields = [
            'reference'    => 50,
            'first_name'   => 50,
            'last_name'    => 50,
            'company_name' => 50,
            'email'        => 50,
            'phone'        => 50,
            'address1'     => 100,
            'address2'     => 100,
            'city'         => 100,
            'postal'       => 50,
            'notes'        => 200,
        ];

        foreach ($stringFields as $field => $maxLength) {
            if (isset($data[$field]) && ! empty($data[$field])) {
                $this->validateString($data[$field], $field, $maxLength);
            }
        }

        // Validate email format
        if (! empty($data['email'])) {
            $this->validateEmail($data['email']);
        }

        // Validate date of birth
        if (! empty($data['dob'])) {
            $this->validateDate($data['dob'], 'Date of birth');
        }

        // Validate gender
        if (! empty($data['gender']) && ! in_array($data['gender'], ['M', 'F'])) {
            throw new ValidationException('Gender must be M or F');
        }

        // Validate country code
        if (! empty($data['country'])) {
            if (strlen($data['country']) !== 2) {
                throw new ValidationException('Country must be a 2-character ISO Alpha-2 code');
            }
        }

        // Validate boolean fields
        $booleanFields = ['marketing_consent_email', 'marketing_consent_text', 'marketing_consent_phone'];
        foreach ($booleanFields as $field) {
            if (isset($data[$field]) && ! is_bool($data[$field])) {
                throw new ValidationException("{$field} must be a boolean value");
            }
        }
    }

    /**
     * Validate search parameters
     */
    private function validateSearchParams(array $params): void
    {
        $validFields   = ['reference', 'email', 'phone'];
        $hasValidField = false;

        foreach ($validFields as $field) {
            if (! empty($params[$field])) {
                $hasValidField = true;

                if ('email' === $field) {
                    $this->validateEmail($params[$field]);
                } else {
                    $this->validateString($params[$field], $field);
                }
            }
        }

        if (! $hasValidField) {
            throw new ValidationException(
                'At least one search parameter is required: ' . implode(', ', $validFields)
            );
        }
    }
}
