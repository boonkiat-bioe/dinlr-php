<?php
namespace Nava\Dinlr\Models;

class Webhook extends AbstractModel
{
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    public function getObject(): ?string
    {
        return $this->getAttribute('object');
    }

    public function getTopic(): ?string
    {
        return $this->getAttribute('topic');
    }

    public function getRestaurantId(): ?string
    {
        return $this->getAttribute('restaurant');
    }

    public function getLocationId(): ?string
    {
        return $this->getAttribute('location');
    }

    public function getCreatedAt(): ?string
    {
        return $this->getAttribute('created_at');
    }

    public function getData(): array
    {
        return $this->getAttribute('data', []);
    }

    public function isOrderEvent(): bool
    {
        return $this->getObject() === 'order';
    }

    public function isCustomerEvent(): bool
    {
        return $this->getObject() === 'customer';
    }

    public function isCreateEvent(): bool
    {
        return strpos($this->getTopic(), '.created') !== false;
    }

    public function isUpdateEvent(): bool
    {
        return strpos($this->getTopic(), '.updated') !== false;
    }

    public function isDeleteEvent(): bool
    {
        return strpos($this->getTopic(), '.deleted') !== false;
    }
}
