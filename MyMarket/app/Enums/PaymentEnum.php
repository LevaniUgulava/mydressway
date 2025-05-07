<?php

namespace App\Enums;

enum PaymentEnum: string
{
    case Pending = 'Pending';
    case Completed = 'Completed';
}
