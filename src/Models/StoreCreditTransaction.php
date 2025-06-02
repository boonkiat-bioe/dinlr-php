<?php
namespace Nava\Dinlr\Models;

class StoreCreditTransaction extends AbstractModel
{
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    public function getCustomer(): ?string
    {
        return $this->getAttribute('customer');
    }

    public function getLocation(): ?string
    {
        return $this->getAttribute('location');
    }

    public function getOrder(): ?string
    {
        return $this->getAttribute('order');
    }

    public function getAmount(): float
    {
        return (float) $this->getAttribute('amount', 0);
    }

    public function getNotes(): ?string
    {
        return $this->getAttribute('notes');
    }

    public function getCreatedAt(): ?string
    {
        return $this->getAttribute('created_at');
    }

    public function isCreditAddition(): bool
    {
        return $this->getAmount() > 0;
    }

    public function isCreditDeduction(): bool
    {
        return $this->getAmount() < 0;
    }

    public function getAbsoluteAmount(): float
    {
        return abs($this->getAmount());
    }
}
