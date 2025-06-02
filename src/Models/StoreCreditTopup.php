<?php
namespace Nava\Dinlr\Models;

class StoreCreditTopup extends AbstractModel
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

    public function getTopupNumber(): ?string
    {
        return $this->getAttribute('topup_no');
    }

    public function getTopupAmount(): float
    {
        return (float) $this->getAttribute('topup_amount', 0);
    }

    public function getPayment(): ?string
    {
        return $this->getAttribute('payment');
    }

    public function getPaymentAmount(): float
    {
        return (float) $this->getAttribute('payment_amount', 0);
    }

    public function getCreatedAt(): ?string
    {
        return $this->getAttribute('created_at');
    }

    public function getBonusAmount(): float
    {
        return $this->getTopupAmount() - $this->getPaymentAmount();
    }

    public function hasBonus(): bool
    {
        return $this->getBonusAmount() > 0;
    }
}
