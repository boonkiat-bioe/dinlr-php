<?php
namespace Nava\Dinlr\Models;

/**
 * Discount model
 */
class Discount extends AbstractModel
{
    /**
     * Get the discount ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    /**
     * Get the discount name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    /**
     * Get the discount type (percent or price)
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->getAttribute('type');
    }

    /**
     * Check if the discount is stackable
     *
     * @return bool
     */
    public function isStackable(): bool
    {
        return (bool) $this->getAttribute('stackable', false);
    }

    /**
     * Check if this is a manufacturer discount
     *
     * @return bool
     */
    public function isManufacturerDiscount(): bool
    {
        return (bool) $this->getAttribute('manufacturer_discount', false);
    }

    /**
     * Get the discount value
     *
     * @return float|null
     */
    public function getValue(): ?float
    {
        return $this->getAttribute('value');
    }

    /**
     * Get the maximum discount value
     *
     * @return float|null
     */
    public function getMaxValue(): ?float
    {
        return $this->getAttribute('max_value');
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
     * Check if this is an open discount (value can be custom defined)
     *
     * @return bool
     */
    public function isOpenDiscount(): bool
    {
        return $this->getValue() === null;
    }

    /**
     * Check if this is a percentage discount
     *
     * @return bool
     */
    public function isPercentDiscount(): bool
    {
        return $this->getType() === 'percent';
    }

    /**
     * Check if this is a price discount
     *
     * @return bool
     */
    public function isPriceDiscount(): bool
    {
        return $this->getType() === 'price';
    }
}
