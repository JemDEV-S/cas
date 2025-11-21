<?php

namespace Modules\Core\Helpers;

use Carbon\Carbon;

/**
 * Date Helper
 *
 * Helper para manejo de fechas.
 */
class DateHelper
{
    public static function now(): Carbon
    {
        return Carbon::now();
    }

    public static function today(): Carbon
    {
        return Carbon::today();
    }

    public static function format(Carbon|string $date, string $format = 'd/m/Y'): string
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->format($format);
    }

    public static function diffForHumans(Carbon|string $date): string
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->diffForHumans();
    }

    public static function isToday(Carbon|string $date): bool
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->isToday();
    }

    public static function isFuture(Carbon|string $date): bool
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->isFuture();
    }

    public static function isPast(Carbon|string $date): bool
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->isPast();
    }

    public static function addDays(Carbon|string $date, int $days): Carbon
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->addDays($days);
    }

    public static function subDays(Carbon|string $date, int $days): Carbon
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->subDays($days);
    }

    public static function diffInDays(Carbon|string $date1, Carbon|string $date2): int
    {
        if (is_string($date1)) {
            $date1 = Carbon::parse($date1);
        }

        if (is_string($date2)) {
            $date2 = Carbon::parse($date2);
        }

        return $date1->diffInDays($date2);
    }

    public static function parse(string $date): Carbon
    {
        return Carbon::parse($date);
    }

    public static function createFromFormat(string $format, string $date): Carbon
    {
        return Carbon::createFromFormat($format, $date);
    }
}
