<?php
namespace Nava\Dinlr\Models;

class LoyaltyMember extends AbstractModel
{
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    public function getPoint(): int
    {
        return (int) $this->getAttribute('point', 0);
    }

    public function getCustomer(): ?string
    {
        return $this->getAttribute('customer');
    }

    public function getUpdatedAt(): ?string
    {
        return $this->getAttribute('updated_at');
    }

    public function hasSufficientPoints(int $requiredPoints): bool
    {
        return $this->getPoint() >= $requiredPoints;
    }

    public function hasPoints(): bool
    {
        return $this->getPoint() > 0;
    }
}
