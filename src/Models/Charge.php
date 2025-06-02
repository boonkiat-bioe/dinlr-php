<?php
namespace Nava\Dinlr\Models;

/**
 * Charge model
 */
class Charge extends AbstractModel
{
    /**
     * Get the charge ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    /**
     * Get the charge name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    /**
     * Get the dining options that apply this charge
     *
     * @return array
     */
    public function getDiningOptions(): array
    {
        return $this->getAttribute('dining_options', []);
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
