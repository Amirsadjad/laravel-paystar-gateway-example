<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case Pending = 'pending';
    case Failed = 'failed';
    case Succeed = 'succeed';
}
