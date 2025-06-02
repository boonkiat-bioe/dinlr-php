<?php
namespace Nava\Dinlr\Models;

class TableSection extends AbstractModel
{
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    public function getUpdatedAt(): ?string
    {
        return $this->getAttribute('updated_at');
    }
}
