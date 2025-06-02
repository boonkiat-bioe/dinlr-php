<?php
namespace Nava\Dinlr\Models;

class LoyaltyMemberCollection extends AbstractCollection
{
    protected $modelClass = LoyaltyMember::class;

    public function findByCustomer(string $customerId): ?LoyaltyMember
    {
        foreach ($this->items as $member) {
            if ($member->getCustomer() === $customerId) {
                return $member;
            }
        }
        return null;
    }

    public function getTotalPoints(): int
    {
        $total = 0;
        foreach ($this->items as $member) {
            $total += $member->getPoint();
        }
        return $total;
    }
}
