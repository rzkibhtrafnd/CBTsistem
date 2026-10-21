<?php

namespace App\Enums;

enum ExamSessionStatus: string
{
    case ONGOING = 'ongoing';
    case FINISHED = 'finished';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
