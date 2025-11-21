<?php

namespace Modules\Core\Helpers;

/**
 * Number Helper
 *
 * Helper para formateo de números.
 */
class NumberHelper
{
    public static function format(float|int $number, int $decimals = 2, string $decPoint = '.', string $thousandsSep = ','): string
    {
        return number_format($number, $decimals, $decPoint, $thousandsSep);
    }

    public static function currency(float $amount, string $currency = 'S/', int $decimals = 2): string
    {
        return $currency . ' ' . self::format($amount, $decimals);
    }

    public static function percentage(float $value, int $decimals = 2): string
    {
        return self::format($value, $decimals) . '%';
    }

    public static function abbreviate(float|int $number, int $decimals = 1): string
    {
        $suffixes = ['', 'K', 'M', 'B', 'T'];
        $suffixIndex = 0;

        while ($number >= 1000 && $suffixIndex < count($suffixes) - 1) {
            $number /= 1000;
            $suffixIndex++;
        }

        return self::format($number, $decimals) . $suffixes[$suffixIndex];
    }

    public static function ordinal(int $number): string
    {
        $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];

        if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
            return $number . 'th';
        }

        return $number . $ends[$number % 10];
    }

    public static function fileSize(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public static function clamp(float|int $value, float|int $min, float|int $max): float|int
    {
        return max($min, min($max, $value));
    }

    public static function randomInt(int $min = 0, int $max = 100): int
    {
        return random_int($min, $max);
    }

    public static function randomFloat(float $min = 0.0, float $max = 1.0, int $decimals = 2): float
    {
        return round($min + mt_rand() / mt_getrandmax() * ($max - $min), $decimals);
    }
}
