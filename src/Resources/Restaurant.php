<?php
namespace Nava\Dinlr\Resources;

use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Models\Restaurant as RestaurantModel;

/**
 * Restaurant resource
 */
class Restaurant extends AbstractResource
{
    /**
     * @var string
     */
    protected $resourcePath = 'onlineorder/restaurant';

    /**
     * Get restaurant details
     *
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @return RestaurantModel
     * @throws ApiException
     */
    public function get(string $restaurantId = null): RestaurantModel
    {

        $path     = $this->buildPath($restaurantId);
        $response = $this->client->request('GET', $path);

        return new RestaurantModel($response['data'] ?? []);
    }
}
