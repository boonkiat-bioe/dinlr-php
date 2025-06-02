<?php
namespace Nava\Dinlr\Resources;

use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Models\PaymentMethod as PaymentMethodModel;
use Nava\Dinlr\Models\PaymentMethodCollection;

/**
 * Payment Method resource
 */
class PaymentMethod extends AbstractResource
{
    /**
     * @var string
     */
    protected $resourcePath = 'onlineorder/payments';

    /**
     * List all payment methods
     *
     * @param string|null $locationId Location ID
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @param array $params Query parameters
     * @return PaymentMethodCollection
     * @throws ApiException
     */
    public function list(string $locationId = null, string $restaurantId = null, array $params = []): PaymentMethodCollection
    {
        $path = $this->buildPath($restaurantId);

        // Add location_id to params if provided
        if (null !== $locationId) {
            $params['location_id'] = $locationId;
        }

        $response = $this->client->request('GET', $path, $params);

        return new PaymentMethodCollection($response['data'] ?? []);
    }

    /**
     * Get a single payment method
     *
     * @param string $paymentMethodId Payment Method ID
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @return PaymentMethodModel
     * @throws ApiException
     */
    public function get(string $paymentMethodId, string $restaurantId = null): PaymentMethodModel
    {
        $path     = $this->buildPath($restaurantId, $paymentMethodId);
        $response = $this->client->request('GET', $path);

        return new PaymentMethodModel($response['data'] ?? []);
    }
}
