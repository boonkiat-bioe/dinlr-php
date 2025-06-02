<?php
namespace Nava\Dinlr\Models;

/**
 * PaymentMethod model
 */
class PaymentMethod extends AbstractModel
{
    /**
     * Get the payment method ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    /**
     * Get the payment method name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    /**
     * Get the sort order
     *
     * @return int|null
     */
    public function getSort(): ?int
    {
        return $this->getAttribute('sort');
    }

    /**
     * Get the payment inputs
     *
     * @return array
     */
    public function getPaymentInputs(): array
    {
        return $this->getAttribute('payment_inputs', []);
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
}
