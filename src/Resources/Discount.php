<?php
namespace Nava\Dinlr\Resources;

use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Models\Discount as DiscountModel;
use Nava\Dinlr\Models\DiscountCollection;

/**
 * Discount resource
 */
class Discount extends AbstractResource
{
    /**
     * @var string
     */
    protected $resourcePath = 'onlineorder/discounts';

    /**
     * List all discounts
     *
     * @param string|null $locationId Location ID
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @param array $params Query parameters
     * @return DiscountCollection
     * @throws ApiException
     */
    public function list(string $locationId = null, string $restaurantId = null, array $params = []): DiscountCollection
    {
        $path = $this->buildPath($restaurantId);

        // Add location_id to params if provided
        if (null !== $locationId) {
            $params['location_id'] = $locationId;
        }

        $response = $this->client->request('GET', $path, $params);

        return new DiscountCollection($response['data'] ?? []);
    }

    /**
     * Get a single discount
     *
     * @param string $discountId Discount ID
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @return DiscountModel
     * @throws ApiException
     */
    public function get(string $discountId, string $restaurantId = null): DiscountModel
    {
        $path     = $this->buildPath($restaurantId, $discountId);
        $response = $this->client->request('GET', $path);

        return new DiscountModel($response['data'] ?? []);
    }
}
