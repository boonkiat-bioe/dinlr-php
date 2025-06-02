<?php
namespace Nava\Dinlr\Models;

class StockTakeCollection extends AbstractCollection
{
    protected $modelClass = StockTake::class;

    public function getOngoing(): array
    {
        $ongoing = [];
        foreach ($this->items as $stockTake) {
            if ($stockTake->isOngoing()) {
                $ongoing[] = $stockTake;
            }
        }
        return $ongoing;
    }

    public function getCompleted(): array
    {
        $completed = [];
        foreach ($this->items as $stockTake) {
            if ($stockTake->isCompleted()) {
                $completed[] = $stockTake;
            }
        }
        return $completed;
    }

    public function getByLocation(string $locationId): array
    {
        $filtered = [];
        foreach ($this->items as $stockTake) {
            if ($stockTake->getLocationId() === $locationId) {
                $filtered[] = $stockTake;
            }
        }
        return $filtered;
    }
}
