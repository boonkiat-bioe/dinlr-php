<?php
namespace Nava\Dinlr\Models;

class CartSummary extends AbstractModel
{
    public function getSubtotal(): float
    {
        return (float) $this->getAttribute('subtotal', 0);
    }

    public function getTotal(): float
    {
        return (float) $this->getAttribute('total', 0);
    }

    public function getFinancialStatus(): ?string
    {
        return $this->getAttribute('financial_status');
    }

    public function getItems(): array
    {
        return $this->getAttribute('items', []);
    }

    public function getDiscounts(): array
    {
        return $this->getAttribute('discounts', []);
    }

    public function getCharges(): array
    {
        return $this->getAttribute('charges', []);
    }

    public function getTaxes(): array
    {
        return $this->getAttribute('taxes', []);
    }

    public function getManufacturerDiscounts(): array
    {
        return $this->getAttribute('manufacturer_discounts', []);
    }

    public function getPayments(): array
    {
        return $this->getAttribute('payments', []);
    }

    public function getRefunds(): array
    {
        return $this->getAttribute('refunds', []);
    }

    public function getObjects(): array
    {
        return $this->getAttribute('objects', []);
    }

    public function getVouchers(): array
    {
        return $this->getAttribute('vouchers', []);
    }

    public function getTotalDiscount(): float
    {
        $total = 0;
        foreach ($this->getDiscounts() as $discount) {
            $total += (float) ($discount['amount'] ?? 0);
        }
        return $total;
    }

    public function getTotalTax(): float
    {
        $total = 0;
        foreach ($this->getTaxes() as $tax) {
            $total += (float) ($tax['amount'] ?? 0);
        }
        return $total;
    }
}
