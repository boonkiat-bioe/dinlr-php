<?php
namespace Nava\Dinlr\Resources;

use Nava\Dinlr\Models\TableSection as TableSectionModel;
use Nava\Dinlr\Models\TableSectionCollection;

class TableSection extends AbstractResource
{
    protected $resourcePath = 'onlineorder/table-sections';

    public function list(string $locationId = null, string $restaurantId = null, array $params = []): TableSectionCollection
    {
        $path = $this->buildPath($restaurantId);

        if (null !== $locationId) {
            $params['location_id'] = $locationId;
        }

        $response = $this->client->request('GET', $path, $params);

        return new TableSectionCollection($response['data'] ?? []);
    }

    public function get(string $tableSectionId, string $restaurantId = null): TableSectionModel
    {
        $path     = $this->buildPath($restaurantId, $tableSectionId);
        $response = $this->client->request('GET', $path);

        return new TableSectionModel($response['data'] ?? []);
    }
}
