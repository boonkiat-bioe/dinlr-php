<?php
namespace Nava\Dinlr\Models;

class Reservation extends AbstractModel
{
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    public function getLocationId(): ?string
    {
        return $this->getAttribute('location');
    }

    public function getCustomerId(): ?string
    {
        return $this->getAttribute('customer');
    }

    public function getReservationNumber(): ?string
    {
        return $this->getAttribute('reservation_no');
    }

    public function getReservationTime(): ?string
    {
        return $this->getAttribute('reservation_time');
    }

    public function getServiceId(): ?string
    {
        return $this->getAttribute('service');
    }

    public function getServiceName(): ?string
    {
        return $this->getAttribute('service_name');
    }

    public function getExperienceId(): ?string
    {
        return $this->getAttribute('experience');
    }

    public function getExperienceName(): ?string
    {
        return $this->getAttribute('experience_name');
    }

    public function getTableSectionId(): ?string
    {
        return $this->getAttribute('table_section');
    }

    public function getTableSectionName(): ?string
    {
        return $this->getAttribute('table_section_name');
    }

    public function getPax(): int
    {
        return (int) $this->getAttribute('pax', 0);
    }

    public function getAdult(): int
    {
        return (int) $this->getAttribute('adult', 0);
    }

    public function getChildren(): int
    {
        return (int) $this->getAttribute('children', 0);
    }

    public function getTotalDeposit(): float
    {
        return (float) $this->getAttribute('total_deposit', 0);
    }

    public function getStatus(): ?string
    {
        return $this->getAttribute('status');
    }

    public function getConfirmBy(): ?string
    {
        return $this->getAttribute('confirm_by');
    }

    public function getNotes(): ?string
    {
        return $this->getAttribute('notes');
    }

    public function getCancelReason(): ?string
    {
        return $this->getAttribute('cancel_reason');
    }

    public function getCreatedAt(): ?string
    {
        return $this->getAttribute('created_at');
    }

    public function getUpdatedAt(): ?string
    {
        return $this->getAttribute('updated_at');
    }

    public function getObjects(): array
    {
        return $this->getAttribute('objects', []);
    }

    public function isBooked(): bool
    {
        return $this->getStatus() === 'booked';
    }

    public function isArrived(): bool
    {
        return $this->getStatus() === 'arrived';
    }

    public function isSeated(): bool
    {
        return $this->getStatus() === 'seated';
    }

    public function isCompleted(): bool
    {
        return $this->getStatus() === 'completed';
    }

    public function isNoShow(): bool
    {
        return $this->getStatus() === 'no_show';
    }

    public function isCancelled(): bool
    {
        return in_array($this->getStatus(), ['cancelled', 'cancelled_payment']);
    }

    public function requiresDeposit(): bool
    {
        return $this->getTotalDeposit() > 0;
    }
}
