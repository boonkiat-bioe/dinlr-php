<?php
namespace Nava\Dinlr\Resources;

use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Models\Category as CategoryModel;
use Nava\Dinlr\Models\CategoryCollection;

/**
 * Category resource
 */
class Category extends AbstractResource
{
    /**
     * @var string
     */
    protected $resourcePath = 'onlineorder/categories';

    /**
     * List all categories
     *
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @param array $params Query parameters
     * @return CategoryCollection
     * @throws ApiException
     */
    public function list(string $restaurantId = null, array $params = []): CategoryCollection
    {
        $path     = $this->buildPath($restaurantId);
        $response = $this->client->request('GET', $path, $params);

        return new CategoryCollection($response['data'] ?? []);
    }

    /**
     * Get a single category
     *
     * @param string $categoryId Category ID
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @return CategoryModel
     * @throws ApiException
     */
    public function get(string $categoryId, string $restaurantId = null): CategoryModel
    {
        $path     = $this->buildPath($restaurantId, $categoryId);
        $response = $this->client->request('GET', $path);

        return new CategoryModel($response['data'] ?? []);
    }
}
