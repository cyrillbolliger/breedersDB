<?php

declare(strict_types=1);

namespace App\Domain\FilterQuery;

enum FilterQueryComparator: string
{
    case Equal = '===';
    case NotEqual = '!==';
    case Less = '<';
    case LessOrEqual = '<=';
    case Greater = '>';
    case GreaterOrEqual = '>=';
    case StartsWith = 'startsWith';
    case StartsNotWith = 'startsNotWith';
    case Contains = 'contains';
    case NotContains = 'notContains';
    case EndsWith = 'endsWith';
    case NotEndsWith = 'notEndsWith';
    case Empty = 'empty';
    case NotEmpty = 'notEmpty';
}
