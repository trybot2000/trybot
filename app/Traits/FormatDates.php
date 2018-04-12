<?php

namespace App\Traits;

trait FormatDates
{
    protected $newDateFormat = 'd.m.Y H:i';

    public function isoDateTimeToMySqlFormat($value)
    {
        $date          = \Carbon\Carbon::parse($value);
        $dateFormatted = null;
        if ($date) {
            $dateFormatted = $date->toDateTimeString();
        }
        return $dateFormatted;
    }
}
