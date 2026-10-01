<?php

namespace App\Support;

/**
 * The real maximum size of an uploaded file: the app's own limit, capped by PHP's
 * upload_max_filesize and post_max_size (a larger file never reaches the app).
 */
final class UploadLimits
{
    /** Effective limit in bytes for a field whose validation rule allows $appKilobytes. */
    public static function bytes(int $appKilobytes): int
    {
        return (int) min($appKilobytes * 1024, self::serverBytes());
    }

    /** Human label, e.g. "20 MB". */
    public static function label(int $appKilobytes): string
    {
        $mb = self::bytes($appKilobytes) / 1048576;

        return ($mb >= 1 ? rtrim(rtrim(number_format($mb, 1), '0'), '.') : '< 1').' MB';
    }

    public static function serverBytes(): int
    {
        return (int) min(self::toBytes(ini_get('upload_max_filesize')), self::toBytes(ini_get('post_max_size')) ?: PHP_INT_MAX);
    }

    private static function toBytes(string|false $value): int
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0') {
            return PHP_INT_MAX;
        }

        $number = (float) $value;

        return (int) match (strtoupper(substr($value, -1))) {
            'G' => $number * 1073741824,
            'M' => $number * 1048576,
            'K' => $number * 1024,
            default => $number,
        };
    }
}
