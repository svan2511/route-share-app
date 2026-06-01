<?php

namespace App\Enums;

enum LoadStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Expired = 'expired';
}
