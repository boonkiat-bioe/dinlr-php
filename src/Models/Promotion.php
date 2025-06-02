<?php
namespace Nava\Dinlr\Models;

/**
 * Promotion model
 */
class Promotion extends AbstractModel
{
    /**
     * Get the promotion ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    /**
     * Get the promotion name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    /**
     * Get the promotion summary
     *
     * @return string|null
     */
    public function getSummary(): ?string
    {
        return $this->getAttribute('summary');
    }

    /**
     * Get the promotion type (auto or voucher)
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->getAttribute('type');
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
     * Check if this is an auto promotion
     *
     * @return bool
     */
    public function isAutoPromotion(): bool
    {
        return $this->getType() === 'auto';
    }

    /**
     * Check if this is a voucher promotion
     *
     * @return bool
     */
    public function isVoucherPromotion(): bool
    {
        return $this->getType() === 'voucher';
    }

    /**
     * Check if the promotion has no end date
     *
     * @return bool
     */
    public function hasNoEndDate(): bool
    {
        return $this->getEndDate() === null;
    }

    /**
     * Check if the promotion is currently active
     * Note: This is a simple check based on dates, actual business logic may vary
     *
     * @return bool
     */
    public function isActive(): bool
    {
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
