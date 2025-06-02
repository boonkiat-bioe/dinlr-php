<?php
namespace Nava\Dinlr\Models;

class StockTake extends AbstractModel
{
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    public function getLocationId(): ?string
    {
        return $this->getAttribute('location');
    }

    public function getNotes(): ?string
    {
        return $this->getAttribute('notes');
    }

    public function getStartDate(): ?string
    {
        return $this->getAttribute('start_date');
    }

    public function getEndDate(): ?string
    {
        return $this->getAttribute('end_date');
    }

    public function getUpdatedAt(): ?string
    {
        return $this->getAttribute('updated_at');
    }

    public function getMaterials(): array
    {
        return $this->getAttribute('materials', []);
    }

    public function isOngoing(): bool
    {
        return $this->getEndDate() === null;
    }

    public function isCompleted(): bool
    {
        return $this->getEndDate() !== null;
    }

    public function getDuration(): ?int
    {
        if (! $this->getStartDate()) {
            return null;
        }

        $start = new \DateTime($this->getStartDate());
        $end   = $this->getEndDate() ? new \DateTime($this->getEndDate()) : new \DateTime();

        return $end->getTimestamp() - $start->getTimestamp();
    }

    public function getMaterialCount(): int
    {
        return count($this->getMaterials());
    }
}
