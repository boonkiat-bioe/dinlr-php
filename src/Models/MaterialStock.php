<?php
namespace Nava\Dinlr\Models;

class MaterialStock extends AbstractModel
{
    public function getMaterialId(): ?string
    {
        return $this->getAttribute('material');
    }

    public function getQuantity(): float
    {
        return (float) $this->getAttribute('qty', 0);
    }

    public function getUpdatedAt(): ?string
    {
        return $this->getAttribute('updated_at');
    }

    public function isInStock(): bool
    {
        return $this->getQuantity() > 0;
    }

    public function isLowStock(float $threshold): bool
    {
        return $this->getQuantity() <= $threshold && $this->isInStock();
    }

    public function isOutOfStock(): bool
    {
        return $this->getQuantity() <= 0;
    }
}
