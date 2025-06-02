<?php
namespace Nava\Dinlr\Models;

/**
 * DiningOption model
 */
class DiningOption extends AbstractModel
{
    /**
     * Get the dining option ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    /**
     * Get the dining option name
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
     * Get the updated at timestamp
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getAttribute('updated_at');
    }
}
