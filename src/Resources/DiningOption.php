<?php
namespace Nava\Dinlr\Resources;

use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Models\DiningOption as DiningOptionModel;
use Nava\Dinlr\Models\DiningOptionCollection;

/**
 * Dining Option resource
 */
class DiningOption extends AbstractResource
{
    /**
     * @var string
     */
    protected $resourcePath = 'onlineorder/dining-options';

    /**
     * List all dining options
     *
     * @param string|null $locationId Location ID
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @param array $params Query parameters
     * @return DiningOptionCollection
     * @throws ApiException
     */
    public function list(?string $locationId = null, ?string $restaurantId = null, array $params = []): DiningOptionCollection
    {
        $path     = $this->buildPath($restaurantId);

        // Add location_id to params if provided
        if (null !== $locationId) {
            $params['location_id'] = $locationId;
        }

        $response = $this->client->request('GET', $path, $params);
        return new DiningOptionCollection($response['data'] ?? []);
    }

    public function listForLocation(string $locationId, ?string $restaurantId = null, array $params = []): DiningOptionCollection
    {
        $params['location_id'] = $locationId;
        return $this->list($locationId, $restaurantId, $params);
    }

    /**
     * Get a single dining option
     *
     * @param string $diningOptionId Dining Option ID
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @return DiningOptionModel
     * @throws ApiException
     */
    public function get(string $diningOptionId, ?string $restaurantId = null): DiningOptionModel
    {
        $path     = $this->buildPath($restaurantId, $diningOptionId);
        $response = $this->client->request('GET', $path);

        return new DiningOptionModel($response['data'] ?? []);
    }
}
