<?php
namespace Nava\Dinlr\Resources;

use Nava\Dinlr\Models\Floorplan as FloorplanModel;
use Nava\Dinlr\Models\FloorplanCollection;

class Floorplan extends AbstractResource
{
    protected $resourcePath = 'onlineorder/floorplans';

    public function list(string $locationId = null, string $restaurantId = null, array $params = []): FloorplanCollection
    {
        $path = $this->buildPath($restaurantId);

        if (null !== $locationId) {
            $params['location_id'] = $locationId;
        }

        $response = $this->client->request('GET', $path, $params);
        return new FloorplanCollection($response['data'] ?? []);
    }

    public function get(string $floorplanId, string $restaurantId = null): FloorplanModel
    {
        $path     = $this->buildPath($restaurantId, $floorplanId);
        $response = $this->client->request('GET', $path);
        return new FloorplanModel($response['data'] ?? []);
    }
}
