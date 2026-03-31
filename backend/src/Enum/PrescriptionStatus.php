<?php

declare(strict_types=1);

namespace App\Enum;

enum PrescriptionStatus: string
{
    case ACTIVE = 'active';
    case USED = 'used';
    case EXPIRED = 'expired';
}
