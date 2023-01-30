<?php

namespace Autotask\Client\Http\Request\Constraints;

use Autotask\Client\Http\Request\Operators\WhereOperator;

final class WhereConstraint implements Constraint
{
    private readonly string $field;

    private readonly WhereOperator $operator;

    private readonly mixed $value;

    private readonly bool $udf;

    public function __construct(
        string $field,
        WhereOperator $operator,
        mixed $value = null,
        bool $udf = false
    ) {
        $this->field = $field;
        $this->operator = $operator;
        $this->value = $value;
        $this->udf = $udf;
    }

    public function toArray(): array
    {
        $constraint = [
            'field' => $this->field,
            'op' => $this->operator->value,
        ];

        if ($this->value !== null) {
            /** @var mixed */
            $constraint['value'] = $this->value;
        }

        if ($this->udf) {
            $constraint['udf'] = true;
        }

        return $constraint;
    }
}