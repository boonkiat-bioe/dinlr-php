<?php
namespace Nava\Dinlr\Models;

class Material extends AbstractModel
{
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    public function getSku(): ?string
    {
        return $this->getAttribute('sku');
    }

    public function getUnit(): ?string
    {
        return $this->getAttribute('unit');
    }

    public function getUpdatedAt(): ?string
    {
        return $this->getAttribute('updated_at');
    }

    public function hasSku(): bool
    {
        return ! empty($this->getSku());
    }
}
