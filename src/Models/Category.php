<?php
namespace Nava\Dinlr\Models;

/**
 * Category model
 */
class Category extends AbstractModel
{
    /**
     * Get the category ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    /**
     * Get the category name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    /**
     * Get the parent category ID
     *
     * @return string|null
     */
    public function getParentCategory(): ?string
    {
        return $this->getAttribute('parent_category');
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
     * Check if this is a top-level category
     *
     * @return bool
     */
    public function isTopLevel(): bool
    {
        return empty($this->getParentCategory());
    }
}
