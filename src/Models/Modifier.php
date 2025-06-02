<?php
namespace Nava\Dinlr\Models;

/**
 * Modifier model
 */
class Modifier extends AbstractModel
{
    /**
     * Get the modifier ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    /**
     * Get the modifier name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    /**
     * Get the minimum selection
     *
     * @return int|null
     */
    public function getMinSelection(): ?int
    {
        return $this->getAttribute('min_selection');
    }

    /**
     * Get the maximum selection
     *
     * @return int|null
     */
    public function getMaxSelection(): ?int
    {
        return $this->getAttribute('max_selection');
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

    /**
     * Get the modifier options
     *
     * @return array
     */
    public function getModifierOptions(): array
    {
        return $this->getAttribute('modifier_options', []);
    }

    /**
     * Check if this modifier is required
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->getMinSelection() > 0;
    }
}
