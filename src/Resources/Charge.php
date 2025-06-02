<?php
namespace Nava\Dinlr\Resources;

use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Models\Charge as ChargeModel;
use Nava\Dinlr\Models\ChargeCollection;

/**
 * Charge resource
 */
class Charge extends AbstractResource
{
    /**
     * @var string
     */
    protected $resourcePath = 'onlineorder/charges';

    /**
     * List all charges
     *
     * @param string|null $locationId Location ID
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @param array $params Query parameters
     * @return ChargeCollection
     * @throws ApiException
     */
    public function list(string $locationId = null, string $restaurantId = null, array $params = []): ChargeCollection
    {
        $path = $this->buildPath($restaurantId);

        // Add location_id to params if provided
        if (null !== $locationId) {
            $params['location_id'] = $locationId;
        }

        $response = $this->client->request('GET', $path, $params);

        return new ChargeCollection($response['data'] ?? []);
    }

    /**
     * Get a single charge
     *
     * @param string $chargeId Charge ID
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @return ChargeModel
     * @throws ApiException
     */
    public function get(string $chargeId, string $restaurantId = null): ChargeModel
    {
        $path     = $this->buildPath($restaurantId, $chargeId);
        $response = $this->client->request('GET', $path);

        return new ChargeModel($response['data'] ?? []);
    }
}
