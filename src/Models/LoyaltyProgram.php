<?php
namespace Nava\Dinlr\Models;

class LoyaltyProgram extends AbstractModel
{
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    public function getName(): ?string
    {
        return $this->getAttribute('name');
    }

    public function getTermSingle(): ?string
    {
        return $this->getAttribute('term_single');
    }

    public function getTermPlural(): ?string
    {
        return $this->getAttribute('term_plural');
    }

    public function getUpdatedAt(): ?string
    {
        return $this->getAttribute('updated_at');
    }

    public function getPointTerm(int $count): string
    {
        if (1 === $count) {
            return $this->getTermSingle() ?: 'point';
        }
        return $this->getTermPlural() ?: 'points';
    }
}
