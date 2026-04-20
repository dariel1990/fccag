<?php

namespace App\Enums;

enum PermissionAction: string
{
    case Create = 'create';
    case Read = 'read';
    case Update = 'update';
    case Delete = 'delete';
    case Reports = 'reports';
    case Export = 'export';
    case Import = 'import';
}
