<?php
namespace Nava\Dinlr\Models;

class StoreCreditTransactionCollection extends AbstractCollection
{
    protected $modelClass = StoreCreditTransaction::class;

    public function getByCustomer(string $customerId): array
    {
        $customerTransactions = [];
        foreach ($this->items as $transaction) {
            if ($transaction->getCustomer() === $customerId) {
                $customerTransactions[] = $transaction;
            }
        }
        return $customerTransactions;
    }

    public function getTotalCreditAmount(): float
    {
        $total = 0;
        foreach ($this->items as $transaction) {
            $total += $transaction->getAmount();
        }
        return $total;
    }

    public function getCreditAdditions(): array
    {
        $additions = [];
        foreach ($this->items as $transaction) {
            if ($transaction->isCreditAddition()) {
                $additions[] = $transaction;
            }
        }
        return $additions;
    }

    public function getCreditDeductions(): array
    {
        $deductions = [];
        foreach ($this->items as $transaction) {
            if ($transaction->isCreditDeduction()) {
                $deductions[] = $transaction;
            }
        }
        return $deductions;
    }

    public function sortByNewest(): self
    {
        usort($this->items, function ($a, $b) {
            return strtotime($b->getCreatedAt()) <=> strtotime($a->getCreatedAt());
        });
        return $this;
    }
}
