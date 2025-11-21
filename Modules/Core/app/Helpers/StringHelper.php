<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Str;

/**
 * String Helper
 *
 * Helper para manipulación de strings.
 */
class StringHelper
{
    public static function slug(string $text): string
    {
        return Str::slug($text);
    }

    public static function truncate(string $text, int $length = 100, string $end = '...'): string
    {
        return Str::limit($text, $length, $end);
    }

    public static function capitalize(string $text): string
    {
        return Str::title($text);
    }

    public static function camelCase(string $text): string
    {
        return Str::camel($text);
    }

    public static function snakeCase(string $text): string
    {
        return Str::snake($text);
    }

    public static function kebabCase(string $text): string
    {
        return Str::kebab($text);
    }

    public static function studlyCase(string $text): string
    {
        return Str::studly($text);
    }

    public static function random(int $length = 16): string
    {
        return Str::random($length);
    }

    public static function contains(string $haystack, string|array $needles): bool
    {
        return Str::contains($haystack, $needles);
    }

    public static function startsWith(string $haystack, string|array $needles): bool
    {
        return Str::startsWith($haystack, $needles);
    }

    public static function endsWith(string $haystack, string|array $needles): bool
    {
        return Str::endsWith($haystack, $needles);
    }

    public static function replace(string $search, string $replace, string $subject): string
    {
        return str_replace($search, $replace, $subject);
    }

    public static function mask(string $string, string $character = '*', int $index = 0, ?int $length = null): string
    {
        return Str::mask($string, $character, $index, $length);
    }

    public static function sanitize(string $text): string
    {
        return htmlspecialchars(strip_tags(trim($text)), ENT_QUOTES, 'UTF-8');
    }
}
