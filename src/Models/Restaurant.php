<?php
namespace Nava\Dinlr\Models;

/**
 * Restaurant model
 */
class Restaurant extends AbstractModel
{
    /**
     * Get the restaurant ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    /**
     * Get the restaurant name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    /**
     * Get the restaurant currency
     *
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->getAttribute('currency');
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
