<?php

namespace App\Support;

use Throwable;

/**
 * The extra line shown on 403/404 error pages: only messages the app wrote for people
 * (abort(404, 'No published manuscripts match these filters.')), never framework internals
 * such as "No query results for model [App\Models\…]".
 */
final class ErrorMessage
{
    private const GENERIC = ['This action is unauthorized.', 'Not Found', 'Forbidden'];

    public static function for(?Throwable $exception): ?string
    {
        $message = trim((string) $exception?->getMessage());

        if ($message === '' || in_array($message, self::GENERIC, true)
            || str_contains($message, 'No query results') || str_contains($message, 'could not be found')
            || str_contains($message, '\\') || str_contains($message, '[')) {
            return null;
        }

        return $message;
    }
}
