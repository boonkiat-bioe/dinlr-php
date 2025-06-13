<?php
namespace Nava\Dinlr\Resources;

use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Models\MenuCollection;

/**
 * Menu resource
 */
class Menu extends AbstractResource
{
    /**
     * @var string
     */
    protected $resourcePath = 'onlineorder/menu';

    /**
     * List all menus with items
     *
     * @param string|null $locationId Location ID
     * @param string|null $restaurantId Restaurant ID (optional, uses config if not provided)
     * @return MenuCollection
     * @throws ApiException
     */
    public function list(?string $locationId = null, ?string $restaurantId = null): MenuCollection
    {
        $path = $this->buildPath($restaurantId);

        $params = [];
        if (null !== $locationId) {
            $params['location_id'] = $locationId;
        }
        else {
            throw new ApiException('Location ID is required to list menus');
        }

        $response = $this->client->request('GET', $path, $params);

        // The response structure has menus nested under data
        $menus = $response['data']['menus'] ?? [];

        return new MenuCollection($menus);
    }
}
