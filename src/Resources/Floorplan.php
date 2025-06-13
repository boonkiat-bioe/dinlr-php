<?php
namespace Nava\Dinlr\Resources;

use Nava\Dinlr\Exception\ApiException;
use Nava\Dinlr\Models\Floorplan as FloorplanModel;
use Nava\Dinlr\Models\FloorplanCollection;
use Nava\Dinlr\Util\SharedCache;

class Floorplan extends AbstractResource
{
    protected $resourcePath = 'onlineorder/floorplans';

    public function list(?string $locationId = null, ?string $restaurantId = null, array $params = []): FloorplanCollection
    {
        $locations = SharedCache::$locations;    
        $found = false;

        // Check if the locationId used is valid
        if (count($locations) > 0 && $locationId !== null) {
            
            foreach ($locations as $location) {
                if ($location['id'] === $locationId) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                throw new ApiException("Location ID \"{$locationId}\" is invalid.");
            }
        }

        $path = $this->buildPath($restaurantId);

        if (null !== $locationId) {
            $params['location_id'] = $locationId;
        }       

        $response = $this->client->request('GET', $path, $params);
        return new FloorplanCollection($response['data'] ?? []);
     
    }

    public function get(string $floorplanId, string $locationId, ?string $restaurantId = null): FloorplanModel
    {
        // Get the Collection
        $floorplans = $this->list($locationId, $restaurantId);

        // Loop through each Discount model in the collection
        foreach ($floorplans as $floorplan) {
            if ($floorplan instanceof FloorplanModel && $floorplan->getId() === $floorplanId) {
                return $floorplan;
            }
        }

        throw new ApiException("Floorplan with ID {$floorplanId} not found.");
    }
}
