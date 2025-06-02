<?php
namespace Nava\Dinlr\Models;

/**
 * Menu model
 */
class Menu extends AbstractModel
{
    /**
     * Get the menu ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    /**
     * Get the menu name
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
     * Get the menu items
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->getAttribute('items', []);
    }

    /**
     * Get the menu times
     *
     * @return array
     */
    public function getTimes(): array
    {
        return $this->getAttribute('times', []);
    }

    /**
     * Check if the menu is available on a specific day
     *
     * @param string $day Day of the week (e.g., 'Monday', 'Tuesday')
     * @return bool
     */
    public function isAvailableOnDay(string $day): bool
    {
        $times = $this->getTimes();

        foreach ($times as $time) {
            if (isset($time['day']) && strcasecmp($time['day'], $day) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the menu time for a specific day
     *
     * @param string $day Day of the week
     * @return array|null
     */
    public function getTimeForDay(string $day): ?array
    {
        $times = $this->getTimes();

        foreach ($times as $time) {
            if (isset($time['day']) && strcasecmp($time['day'], $day) === 0) {
                return $time;
            }
        }

        return null;
    }

    /**
     * Get the total number of items in the menu
     *
     * @return int
     */
    public function getItemCount(): int
    {
        return count($this->getItems());
    }
}
