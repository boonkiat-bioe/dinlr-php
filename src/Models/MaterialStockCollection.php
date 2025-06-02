<?php
namespace Nava\Dinlr\Models;

class MaterialStockCollection extends AbstractCollection
{
    protected $modelClass = MaterialStock::class;

    public function getByMaterial(string $materialId): ?MaterialStock
    {
        foreach ($this->items as $stock) {
            if ($stock->getMaterialId() === $materialId) {
                return $stock;
            }
        }
        return null;
    }

    public function getOutOfStock(): array
    {
        $outOfStock = [];
        foreach ($this->items as $stock) {
            if ($stock->isOutOfStock()) {
                $outOfStock[] = $stock;
            }
        }
        return $outOfStock;
    }

    public function getLowStock(float $threshold): array
    {
        $lowStock = [];
        foreach ($this->items as $stock) {
            if ($stock->isLowStock($threshold)) {
                $lowStock[] = $stock;
            }
        }
        return $lowStock;
    }

    public function getTotalItems(): int
    {
        return count($this->items);
    }

    public function getTotalQuantity(): float
    {
        $total = 0;
        foreach ($this->items as $stock) {
            $total += $stock->getQuantity();
        }
        return $total;
    }
}
