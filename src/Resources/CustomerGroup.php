<?php
namespace Nava\Dinlr\Resources;

use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Models\CustomerGroup as CustomerGroupModel;
use Nava\Dinlr\Models\CustomerGroupCollection;

/**
 * Customer Group resource
 */
class CustomerGroup extends AbstractResource
{
    /**
     * @var string
     */
    protected $resourcePath = 'onlineorder/customer-groups';

    /**
     * List all customer groups
     *
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @param array $params Query parameters
     * @return CustomerGroupCollection
     * @throws ApiException
     */
    public function list(string $restaurantId = null, array $params = []): CustomerGroupCollection
    {
        $path     = $this->buildPath($restaurantId);
        $response = $this->client->request('GET', $path, $params);

        return new CustomerGroupCollection($response['data'] ?? []);
    }

    /**
     * Get a single customer group
     *
     * @param string $groupId Customer Group ID
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @return CustomerGroupModel
     * @throws ApiException
     */
    public function get(string $groupId, string $restaurantId = null): CustomerGroupModel
    {
        $path     = $this->buildPath($restaurantId, $groupId);
        $response = $this->client->request('GET', $path);

        return new CustomerGroupModel($response['data'] ?? []);
    }
}
