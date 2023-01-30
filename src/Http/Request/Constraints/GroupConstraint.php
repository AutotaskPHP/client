<?php

namespace Autotask\Client\Http\Request\Constraints;

use Autotask\Client\Http\Request\Operators\GroupOperator;

final class GroupConstraint implements Constraint
{
    private readonly GroupOperator $operator;

    /** @var array<array-key,Constraint> $constraints */
    private readonly array $constraints;

    public function __construct(GroupOperator $operator, Constraint ...$constraints)
    {
        $this->operator = $operator;
        $this->constraints = $constraints;
    }

    public function toArray(): array
    {
        $constraints = [];

        foreach ($this->constraints as $constraint) {
            $constraints[] = $constraint->toArray();
        }

        return [
            'op' => $this->operator->value,
            'items' => $constraints,
        ];
    }
}