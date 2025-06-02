<?php
namespace Nava\Dinlr\Models;

class LoyaltyTransaction extends AbstractModel
{
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    public function getMember(): ?string
    {
        return $this->getAttribute('member');
    }

    public function getLocation(): ?string
    {
        return $this->getAttribute('location');
    }

    public function getOrder(): ?string
    {
        return $this->getAttribute('order');
    }

    public function getPoint(): int
    {
        return (int) $this->getAttribute('point', 0);
    }

    public function getNotes(): ?string
    {
        return $this->getAttribute('notes');
    }

    public function getCreatedAt(): ?string
    {
        return $this->getAttribute('created_at');
    }

    public function isPointAddition(): bool
    {
        return $this->getPoint() > 0;
    }

    public function isPointDeduction(): bool
    {
        return $this->getPoint() < 0;
    }

    public function getAbsolutePoints(): int
    {
        return abs($this->getPoint());
    }
}
