<?php
namespace Nava\Dinlr\Resources;

use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Exception\ValidationException;
use Nava\Dinlr\Models\StoreCreditBalance;
use Nava\Dinlr\Models\StoreCreditTopup;
use Nava\Dinlr\Models\StoreCreditTransaction;
use Nava\Dinlr\Models\StoreCreditTransactionCollection;

class StoreCredit extends AbstractResource
{
    protected $resourcePath = 'onlineorder/store-credit';

    public function getCustomerBalance(string $customerId, string $restaurantId = null): StoreCreditBalance
    {
        if (empty($customerId)) {
            throw new ValidationException('Customer ID is required');
        }

        $path     = $this->buildPath($restaurantId, "customers/{$customerId}");
        $response = $this->client->request('GET', $path);

        return new StoreCreditBalance($response['data'] ?? []);
    }

    public function createTransaction(array $transactionData, string $restaurantId = null): StoreCreditTransaction
    {
        $this->validateRequired($transactionData, ['customer', 'amount']);

        if (! is_numeric($transactionData['amount'])) {
            throw new ValidationException('Amount must be a numeric value');
        }

        if (isset($transactionData['notes']) && strlen($transactionData['notes']) > 200) {
            throw new ValidationException('Notes cannot exceed 200 characters');
        }

        $path     = $this->buildPath($restaurantId, 'transactions');
        $response = $this->client->request('POST', $path, $transactionData);

        return new StoreCreditTransaction($response['data'] ?? []);
    }

    public function searchTransactions(array $searchParams = [], string $restaurantId = null): StoreCreditTransactionCollection
    {
        if (isset($searchParams['limit']) && ($searchParams['limit'] < 1 || $searchParams['limit'] > 200)) {
            throw new ValidationException('Limit must be between 1 and 200');
        }

        $path     = $this->buildPath($restaurantId, 'transactions/search');
        $response = $this->client->request('GET', $path, $searchParams);

        return new StoreCreditTransactionCollection($response['data'] ?? []);
    }

    public function createTopup(array $topupData, string $restaurantId = null): StoreCreditTopup
    {
        $this->validateRequired($topupData, ['customer', 'topup_amount', 'payment', 'payment_amount']);

        if (! is_numeric($topupData['topup_amount']) || $topupData['topup_amount'] <= 0) {
            throw new ValidationException('Topup amount must be a positive number');
        }

        if (! is_numeric($topupData['payment_amount']) || $topupData['payment_amount'] <= 0) {
            throw new ValidationException('Payment amount must be a positive number');
        }

        if (isset($topupData['topup_no'])) {
            if (strlen($topupData['topup_no']) > 10) {
                throw new ValidationException('Topup number cannot exceed 10 characters');
            }
            if (! preg_match('/^[a-zA-Z0-9]+$/', $topupData['topup_no'])) {
                throw new ValidationException('Topup number must be alphanumeric with no spaces');
            }
        }

        $path     = $this->buildPath($restaurantId, 'topups');
        $response = $this->client->request('POST', $path, $topupData);

        return new StoreCreditTopup($response['data'] ?? []);
    }

    // Convenience methods
    public function addCredit(string $customerId, float $amount, string $notes = null, string $locationId = null, string $restaurantId = null): StoreCreditTransaction
    {
        $transactionData = [
            'customer' => $customerId,
            'amount'   => abs($amount),
        ];

        if ($notes) {
            $transactionData['notes'] = $notes;
        }

        if ($locationId) {
            $transactionData['location'] = $locationId;
        }

        return $this->createTransaction($transactionData, $restaurantId);
    }

    public function deductCredit(string $customerId, float $amount, string $notes = null, string $locationId = null, string $restaurantId = null): StoreCreditTransaction
    {
        $transactionData = [
            'customer' => $customerId,
            'amount'   => -abs($amount),
        ];

        if ($notes) {
            $transactionData['notes'] = $notes;
        }

        if ($locationId) {
            $transactionData['location'] = $locationId;
        }

        return $this->createTransaction($transactionData, $restaurantId);
    }

    public function getCustomerTransactions(string $customerId, string $restaurantId = null, array $params = []): StoreCreditTransactionCollection
    {
        $params['customer_id'] = $customerId;
        return $this->searchTransactions($params, $restaurantId);
    }

    public function getLocationTransactions(string $locationId, string $restaurantId = null, array $params = []): StoreCreditTransactionCollection
    {
        $params['location_id'] = $locationId;
        return $this->searchTransactions($params, $restaurantId);
    }

    public function getCurrentAppTransactions(string $restaurantId = null, array $params = []): StoreCreditTransactionCollection
    {
        $params['app_id'] = 'current';
        return $this->searchTransactions($params, $restaurantId);
    }

    public function getBulkCustomerBalances(array $customerIds, string $restaurantId = null): array
    {
        $balances = [];

        foreach ($customerIds as $customerId) {
            try {
                $balance               = $this->getCustomerBalance($customerId, $restaurantId);
                $balances[$customerId] = $balance;
            } catch (ApiException $e) {
                if ($e->getCode() !== 404) {
                    throw $e;
                }
            }
        }

        return $balances;
    }

    /**
     * Get transactions by date range
     *
     * @param string $startDate Start date (ISO 8601)
     * @param string $endDate End date (ISO 8601)
     * @param string|null $restaurantId Restaurant ID (uses config default if null)
     * @param array $params Additional parameters
     * @return StoreCreditTransactionCollection
     * @throws ApiException
     */
    public function getTransactionsByDateRange(string $startDate, string $endDate, string $restaurantId = null, array $params = []): StoreCreditTransactionCollection
    {
        $params['created_at_min'] = $startDate;
        $params['created_at_max'] = $endDate;

        return $this->searchTransactions($params, $restaurantId);
    }
}
