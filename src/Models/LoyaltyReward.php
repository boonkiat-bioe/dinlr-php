<?php
namespace Nava\Dinlr\Models;

class LoyaltyReward extends AbstractModel
{
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    public function getPoint(): int
    {
        return (int) $this->getAttribute('point', 0);
    }

    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    public function canBeRedeemedBy(LoyaltyMember $member): bool
    {
        return $member->hasSufficientPoints($this->getPoint());
    }
}
