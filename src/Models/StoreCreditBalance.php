<?php
namespace Nava\Dinlr\Models;

class StoreCreditBalance extends AbstractModel
{
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    public function getStoreCredit(): float
    {
        return (float) $this->getAttribute('store_credit', 0);
    }

    public function getUpdatedAt(): ?string
    {
        return $this->getAttribute('updated_at');
    }

    public function hasStoreCredit(): bool
    {
        return $this->getStoreCredit() > 0;
    }

    public function hasSufficientCredit(float $amount): bool
    {
        return $this->getStoreCredit() >= $amount;
    }
}
