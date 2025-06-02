<?php
namespace Nava\Dinlr\Models;

class Service extends AbstractModel
{
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    public function getExperienceId(): ?string
    {
        return $this->getAttribute('experience');
    }

    public function getTableSectionId(): ?string
    {
        return $this->getAttribute('table_section');
    }

    public function getAvailableTimes(): array
    {
        return $this->getAttribute('available_times', []);
    }

    public function hasAvailability(): bool
    {
        return count($this->getAvailableTimes()) > 0;
    }
}
