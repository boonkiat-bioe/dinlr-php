<?php
namespace Nava\Dinlr\Models;

class ServiceCollection extends AbstractCollection
{
    protected $modelClass = Service::class;

    public function getByExperience(string $experienceId): array
    {
        $filtered = [];
        foreach ($this->items as $service) {
            if ($service->getExperienceId() === $experienceId) {
                $filtered[] = $service;
            }
        }
        return $filtered;
    }

    public function getAvailable(): array
    {
        $available = [];
        foreach ($this->items as $service) {
            if ($service->hasAvailability()) {
                $available[] = $service;
            }
        }
        return $available;
    }
}
