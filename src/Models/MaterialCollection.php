<?php
namespace Nava\Dinlr\Models;

class MaterialCollection extends AbstractCollection
{
    protected $modelClass = Material::class;

    public function findBySku(string $sku): ?Material
    {
        foreach ($this->items as $material) {
            if ($material->getSku() === $sku) {
                return $material;
            }
        }
        return null;
    }

    public function getByUnit(string $unit): array
    {
        $filtered = [];
        foreach ($this->items as $material) {
            if ($material->getUnit() === $unit) {
                $filtered[] = $material;
            }
        }
        return $filtered;
    }
}
