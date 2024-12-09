<?php

namespace App\Enums;

enum TopicStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}

