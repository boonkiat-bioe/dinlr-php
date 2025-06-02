<?php
namespace Nava\Dinlr\Models;

/**
 * Item model
 */
class Item extends AbstractModel
{
    /**
     * Get the item ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    /**
     * Get the item number
     *
     * @return string|null
     */
    public function getItemNumber(): ?string
    {
        return $this->getAttribute('item_no');
    }

    /**
     * Get the item name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    /**
     * Get the item description
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->getAttribute('description');
    }

    /**
     * Get the item image URL
     *
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->getAttribute('image');
    }

    /**
     * Get the category ID
     *
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->getAttribute('category');
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
     * Get the item variants
     *
     * @return array
     */
    public function getVariants(): array
    {
        return $this->getAttribute('variants', []);
    }
    /**
     * Get the item modifiers
     *
     * @return array
     */
    public function getModifiers(): array
    {
        return $this->getAttribute('modifiers', []);
    }
}
