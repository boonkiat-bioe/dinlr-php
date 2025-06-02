<?php
namespace Nava\Dinlr\Models;

class ReservationCollection extends AbstractCollection
{
    protected $modelClass = Reservation::class;

    public function getByStatus(string $status): array
    {
        $filtered = [];
        foreach ($this->items as $reservation) {
            if ($reservation->getStatus() === $status) {
                $filtered[] = $reservation;
            }
        }
        return $filtered;
    }

    public function getUpcoming(): array
    {
        $upcoming = [];
        $now      = new \DateTime();

        foreach ($this->items as $reservation) {
            $reservationTime = new \DateTime($reservation->getReservationTime());
            if ($reservationTime > $now && ! $reservation->isCancelled()) {
                $upcoming[] = $reservation;
            }
        }

        return $upcoming;
    }

    public function getTotalPax(): int
    {
        $total = 0;
        foreach ($this->items as $reservation) {
            $total += $reservation->getPax();
        }
        return $total;
    }
}
