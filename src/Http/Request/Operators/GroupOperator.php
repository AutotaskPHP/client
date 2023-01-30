<?php

namespace Autotask\Client\Http\Request\Operators;

enum GroupOperator: string
{
    case And = 'AND';
    case Or = 'OR';
}