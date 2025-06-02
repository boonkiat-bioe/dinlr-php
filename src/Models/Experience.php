<?php
namespace Nava\Dinlr\Models;

class Experience extends AbstractModel
{
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    public function getSort(): ?int
    {
        return $this->getAttribute('sort');
    }

    public function getUpdatedAt(): ?string
    {
        return $this->getAttribute('updated_at');
    }
}
