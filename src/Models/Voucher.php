<?php
namespace Nava\Dinlr\Models;

/**
 * Voucher model
 */
class Voucher extends AbstractModel
{
    /**
     * Get the voucher ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    /**
     * Get the voucher code
     *
     * @return string|null
     */
    public function getVoucherCode(): ?string
    {
        return $this->getAttribute('voucher_code');
    }

    /**
     * Get the applicable type
     *
     * @return string|null
     */
    public function getApplicable(): ?string
    {
        return $this->getAttribute('applicable');
    }

    /**
     * Get the voucher type (discount or promotion)
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->getAttribute('type');
    }

    /**
     * Get the discount ID
     *
     * @return string|null
     */
    public function getDiscountId(): ?string
    {
        return $this->getAttribute('discount');
    }

    /**
     * Get the promotion ID
     *
     * @return string|null
     */
    public function getPromotionId(): ?string
    {
        return $this->getAttribute('promotion');
    }

    /**
     * Get the customer ID
     *
     * @return string|null
     */
    public function getCustomerId(): ?string
    {
        return $this->getAttribute('customer');
    }

    /**
     * Get the number of times redeemed
     *
     * @return int
     */
    public function getRedeemed(): int
    {
        return (int) $this->getAttribute('redeemed', 0);
    }

    /**
     * Get the maximum redemptions
     *
     * @return int|null
     */
    public function getMaxRedemptions(): ?int
    {
        return $this->getAttribute('max_redemptions');
    }

    /**
     * Get the start date
     *
     * @return string|null
     */
    public function getStartDate(): ?string
    {
        return $this->getAttribute('start_date');
    }

    /**
     * Get the end date
     *
     * @return string|null
     */
    public function getEndDate(): ?string
    {
        return $this->getAttribute('end_date');
    }

    /**
     * Get the updated at timestamp
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getAttribute('updated_at');
    }

    /**
     * Check if this is a discount voucher
     *
     * @return bool
     */
    public function isDiscountVoucher(): bool
    {
        return $this->getType() === 'discount';
    }

    /**
     * Check if this is a promotion voucher
     *
     * @return bool
     */
    public function isPromotionVoucher(): bool
    {
        return $this->getType() === 'promotion';
    }

    /**
     * Check if this is a customer-specific voucher
     *
     * @return bool
     */
    public function isCustomerVoucher(): bool
    {
        return $this->getApplicable() === 'customer';
    }

    /**
     * Check if the voucher has unlimited redemptions
     *
     * @return bool
     */
    public function hasUnlimitedRedemptions(): bool
    {
        return $this->getMaxRedemptions() === null;
    }

    /**
     * Check if the voucher has no end date
     *
     * @return bool
     */
    public function hasNoEndDate(): bool
    {
        return $this->getEndDate() === null;
    }

    /**
     * Check if the voucher can still be redeemed
     *
     * @return bool
     */
    public function canBeRedeemed(): bool
    {
        // Check max redemptions
        if (! $this->hasUnlimitedRedemptions() && $this->getRedeemed() >= $this->getMaxRedemptions()) {
            return false;
        }

        // Check dates
        $now = new \DateTime();

        if ($this->getStartDate()) {
            $startDate = new \DateTime($this->getStartDate());
            if ($now < $startDate) {
                return false;
            }
        }

        if ($this->getEndDate()) {
            $endDate = new \DateTime($this->getEndDate());
            if ($now > $endDate) {
                return false;
            }
        }

        return true;
    }
}
