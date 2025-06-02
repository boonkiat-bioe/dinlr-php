<?php
namespace Nava\Dinlr\Resources;

use Nava\Dinlr\Models\Experience as ExperienceModel;
use Nava\Dinlr\Models\ExperienceCollection;

class Experience extends AbstractResource
{
    protected $resourcePath = 'onlineorder/experiences';

    public function list(string $locationId = null, string $restaurantId = null, array $params = []): ExperienceCollection
    {
        $path = $this->buildPath($restaurantId);

        if (null !== $locationId) {
            $params['location_id'] = $locationId;
        }

        $response = $this->client->request('GET', $path, $params);

        return new ExperienceCollection($response['data'] ?? []);
    }

    public function get(string $experienceId, string $restaurantId = null): ExperienceModel
    {
        $path     = $this->buildPath($restaurantId, $experienceId);
        $response = $this->client->request('GET', $path);

        return new ExperienceModel($response['data'] ?? []);
    }
}
