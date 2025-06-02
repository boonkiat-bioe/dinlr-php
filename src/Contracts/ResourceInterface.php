<?php
namespace Nava\Dinlr\Contracts;

interface ResourceInterface
{
    /**
     * Standard list method signature - ALWAYS restaurantId first, then params
     * Location-specific resources should use params['location_id']
     */
    public function list(string $restaurantId = null, array $params = []);

    /**
     * Standard get method signature
     */
    public function get(string $id, string $restaurantId = null);
}
