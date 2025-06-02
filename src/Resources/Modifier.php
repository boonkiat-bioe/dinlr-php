<?php
namespace Nava\Dinlr\Resources;

use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Models\Modifier as ModifierModel;
use Nava\Dinlr\Models\ModifierCollection;

/**
 * Modifier resource
 */
class Modifier extends AbstractResource
{
    /**
     * @var string
     */
    protected $resourcePath = 'onlineorder/modifiers';

    /**
     * List all modifiers
     *
     * @param string|null $locationId Location ID
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @param array $params Query parameters
     * @return ModifierCollection
     * @throws ApiException
     */
    public function list(string $locationId = null, string $restaurantId = null, array $params = []): ModifierCollection
    {
        $path = $this->buildPath($restaurantId);

        // Add location_id to params if provided
        if (null !== $locationId) {
            $params['location_id'] = $locationId;
        }

        $response = $this->client->request('GET', $path, $params);

        return new ModifierCollection($response['data'] ?? []);
    }

    /**
     * Get a single modifier
     *
     * @param string $modifierId Modifier ID
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @return ModifierModel
     * @throws ApiException
     */
    public function get(string $modifierId, string $restaurantId = null): ModifierModel
    {
        $path     = $this->buildPath($restaurantId, $modifierId);
        $response = $this->client->request('GET', $path);

        return new ModifierModel($response['data'] ?? []);
    }
}
