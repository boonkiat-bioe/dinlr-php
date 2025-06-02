<?php
namespace Nava\Dinlr\Models;

class LoyaltyRewardCollection extends AbstractCollection
{
    protected $modelClass = LoyaltyReward::class;

    public function getRedeemableBy(LoyaltyMember $member): array
    {
        $redeemable = [];
        foreach ($this->items as $reward) {
            if ($reward->canBeRedeemedBy($member)) {
                $redeemable[] = $reward;
            }
        }
        return $redeemable;
    }

    public function sortByPoints(): self
    {
        usort($this->items, function ($a, $b) {
            return $a->getPoint() <=> $b->getPoint();
        });
        return $this;
    }
}
