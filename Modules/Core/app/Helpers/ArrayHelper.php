<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Arr;

/**
 * Array Helper
 *
 * Helper para operaciones con arrays.
 */
class ArrayHelper
{
    public static function get(array $array, string $key, $default = null)
    {
        return Arr::get($array, $key, $default);
    }

    public static function set(array &$array, string $key, $value): array
    {
        Arr::set($array, $key, $value);
        return $array;
    }

    public static function has(array $array, string|array $keys): bool
    {
        return Arr::has($array, $keys);
    }

    public static function only(array $array, array $keys): array
    {
        return Arr::only($array, $keys);
    }

    public static function except(array $array, array $keys): array
    {
        return Arr::except($array, $keys);
    }

    public static function pluck(array $array, string $value, ?string $key = null): array
    {
        return Arr::pluck($array, $value, $key);
    }

    public static function flatten(array $array): array
    {
        return Arr::flatten($array);
    }

    public static function wrap($value): array
    {
        return Arr::wrap($value);
    }

    public static function first(array $array, ?callable $callback = null, $default = null)
    {
        return Arr::first($array, $callback, $default);
    }

    public static function last(array $array, ?callable $callback = null, $default = null)
    {
        return Arr::last($array, $callback, $default);
    }

    public static function where(array $array, callable $callback): array
    {
        return Arr::where($array, $callback);
    }

    public static function sortBy(array $array, string $key, bool $descending = false): array
    {
        $sorted = collect($array)->sortBy($key, SORT_REGULAR, $descending)->values()->all();
        return $sorted;
    }

    public static function groupBy(array $array, string $key): array
    {
        return collect($array)->groupBy($key)->all();
    }

    public static function chunk(array $array, int $size): array
    {
        return array_chunk($array, $size);
    }

    public static function unique(array $array, ?string $key = null): array
    {
        if ($key) {
            return collect($array)->unique($key)->values()->all();
        }

        return array_values(array_unique($array));
    }
}
