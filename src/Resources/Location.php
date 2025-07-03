<?php
namespace Nava\Dinlr\Resources;

use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Models\Location as LocationModel;
use Nava\Dinlr\Models\LocationCollection;

/**
 * Location resource
 */
class Location extends AbstractResource
{
    /**
     * @var string
     */
    protected $resourcePath = 'onlineorder/locations';

    /**
     * List all locations
     *
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @param array $params Query parameters
     * @return LocationCollection
     * @throws ApiException
     */
    public function list(?string $restaurantId = null, array $params = []): LocationCollection
    {
        $path     = $this->buildPath($restaurantId);
        $response = $this->client->request('GET', $path, $params);

        return new LocationCollection($response['data'] ?? []);
    }

    /**
     * Get a single location
     *
     * @param string $locationId Location ID
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @return LocationModel
     * @throws ApiException
     */
    public function get(string $locationId, ?string $restaurantId = null): LocationModel
    {
        $path     = $this->buildPath($restaurantId, $locationId);
        $response = $this->client->request('GET', $path);

        return new LocationModel($response['data'] ?? []);
    }
}
