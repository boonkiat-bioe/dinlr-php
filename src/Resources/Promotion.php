<?php
namespace Nava\Dinlr\Resources;

use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Models\Promotion as PromotionModel;
use Nava\Dinlr\Models\PromotionCollection;

/**
 * Promotion resource
 */
class Promotion extends AbstractResource
{
    /**
     * @var string
     */
    protected $resourcePath = 'onlineorder/promotions';

    /**
     * List all promotions
     *
     * @param string|null $locationId Location ID
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @param array $params Query parameters
     * @return PromotionCollection
     * @throws ApiException
     */
    public function list(?string $locationId = null, ?string $restaurantId = null, array $params = []): PromotionCollection
    {
        $path = $this->buildPath($restaurantId);

        // Add location_id to params if provided
        if (null !== $locationId) {
            $params['location_id'] = $locationId;
        }

        $response = $this->client->request('GET', $path, $params);

        return new PromotionCollection($response['data'] ?? []);
    }

    /**
     * Get a single promotion
     *
     * @param string $promotionId Promotion ID
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @return PromotionModel
     * @throws ApiException
     */
    public function get(string $promotionId, ?string $locationId = null, ?string $restaurantId = null): PromotionModel
    {
        // Get the PromotionCollection
        $promotions = $this->list($locationId, $restaurantId);

        // Loop through each Promotion model in the collection
        foreach ($promotions as $promotion) {
            if ($promotion instanceof PromotionModel && $promotion->getId() === $promotionId) {
                return $promotion;
            }
        }

        throw new ApiException("Promotion with ID {$promotionId} not found.");
    }
}
