<?php
namespace Nava\Dinlr\Models;

class LoyaltyProgramCollection extends AbstractCollection
{
    protected $modelClass = LoyaltyProgram::class;

    public function findByName(string $name): ?LoyaltyProgram
    {
        foreach ($this->items as $program) {
            if (strcasecmp($program->getName(), $name) === 0) {
                return $program;
            }
        }
        return null;
    }
}
