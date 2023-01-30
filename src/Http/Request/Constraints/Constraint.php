<?php

namespace Autotask\Client\Http\Request\Constraints;

interface Constraint
{
    public function toArray(): array;
}