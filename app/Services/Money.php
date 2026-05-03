<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Tiền tệ dạng chuỗi decimal, scale 2 (VND).
 */
final class Money
{
    private const SCALE = 2;

    public static function add(string $a, string $b): string
    {
        return bcadd($a, $b, self::SCALE);
    }

    public static function sub(string $a, string $b): string
    {
        return bcsub($a, $b, self::SCALE);
    }

    public static function mul(string $a, string $b): string
    {
        return bcmul($a, $b, self::SCALE);
    }

    public static function cmp(string $a, string $b): int
    {
        return bccomp($a, $b, self::SCALE);
    }

    public static function isZero(string $a): bool
    {
        return self::cmp($a, '0.00') === 0;
    }

    public static function isPositive(string $a): bool
    {
        return self::cmp($a, '0.00') > 0;
    }

    public static function normalize(mixed $value): string
    {
        if (is_string($value)) {
            return bcadd($value, '0', self::SCALE);
        }

        return bcadd((string) $value, '0', self::SCALE);
    }
}
