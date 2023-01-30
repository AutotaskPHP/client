<?php

namespace Autotask\Client\Http\Request\Operators;

enum WhereOperator: string
{
    case Equals = 'eq';
    case NotEquals = 'noteq';
    case GreaterThan = 'gt';
    case GreaterThanOrEqualTo = 'gte';
    case LessThan = 'lt';
    case LessThanOrEqualTo = 'lte';
    case BeginsWith = 'beginsWith';
    case EndsWith = 'endsWith';
    case Contains = 'contains';
    case Exist = 'exist';
    case NotExist = 'notExist';
    case In = 'in';
    case NotIn = 'notIn';
}