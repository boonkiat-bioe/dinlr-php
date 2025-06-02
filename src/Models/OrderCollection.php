<?php
namespace Nava\Dinlr\Models;

class OrderCollection extends AbstractCollection
{
    protected $modelClass = Order::class;

    public function getByStatus(string $status): array
    {
        $filtered = [];
        foreach ($this->items as $order) {
            if ($order->getStatus() === $status) {
                $filtered[] = $order;
            }
        }
        return $filtered;
    }

    public function getByFinancialStatus(string $status): array
    {
        $filtered = [];
        foreach ($this->items as $order) {
            if ($order->getFinancialStatus() === $status) {
                $filtered[] = $order;
            }
        }
        return $filtered;
    }

    public function getTotalRevenue(): float
    {
        $total = 0;
        foreach ($this->items as $order) {
            $total += $order->getTotal();
        }
        return $total;
    }
}
