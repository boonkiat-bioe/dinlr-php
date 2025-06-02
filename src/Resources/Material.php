<?php
namespace Nava\Dinlr\Resources;

use Nava\Dinlr\Models\Material as MaterialModel;
use Nava\Dinlr\Models\MaterialCollection;
use Nava\Dinlr\Models\MaterialStockCollection;
use Nava\Dinlr\Models\StockTakeCollection;

class Material extends AbstractResource
{
    protected $resourcePath = 'onlineorder/materials';

    public function list(string $locationId = null, string $restaurantId = null, array $params = []): MaterialCollection
    {
        $path = $this->buildPath($restaurantId);

        if (null !== $locationId) {
            $params['location_id'] = $locationId;
        }

        $response = $this->client->request('GET', $path, $params);

        return new MaterialCollection($response['data'] ?? []);
    }

    public function get(string $materialId, string $restaurantId = null): MaterialModel
    {
        $path     = $this->buildPath($restaurantId, $materialId);
        $response = $this->client->request('GET', $path);

        return new MaterialModel($response['data'] ?? []);
    }

    public function getStockLevels(string $locationId, string $restaurantId = null, array $params = []): MaterialStockCollection
    {
        $params['location_id'] = $locationId;
        $path                  = str_replace('/materials', '/material-stocks', $this->buildPath($restaurantId));
        $response              = $this->client->request('GET', $path, $params);

        return new MaterialStockCollection($response['data'] ?? []);
    }

    public function getStockTakes(string $restaurantId = null, array $params = []): StockTakeCollection
    {
        $path     = str_replace('/materials', '/stock-takes', $this->buildPath($restaurantId));
        $response = $this->client->request('GET', $path, $params);

        return new StockTakeCollection($response['data'] ?? []);
    }
}
