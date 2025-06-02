<?php
namespace Nava\Dinlr\Models;

class LoyaltyTransactionCollection extends AbstractCollection
{
    protected $modelClass = LoyaltyTransaction::class;

    public function getByMember(string $memberId): array
    {
        $memberTransactions = [];
        foreach ($this->items as $transaction) {
            if ($transaction->getMember() === $memberId) {
                $memberTransactions[] = $transaction;
            }
        }
        return $memberTransactions;
    }

    public function getTotalPoints(): int
    {
        $total = 0;
        foreach ($this->items as $transaction) {
            $total += $transaction->getPoint();
        }
        return $total;
    }

    public function sortByNewest(): self
    {
        usort($this->items, function ($a, $b) {
            return strtotime($b->getCreatedAt()) <=> strtotime($a->getCreatedAt());
        });
        return $this;
    }
}
