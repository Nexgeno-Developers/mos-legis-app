<?php

namespace App\Http\Middleware;

use App\Support\UploadLimits;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * When PHP drops a file for being over its upload limit, the app would otherwise see an empty
 * field ("The manuscript field is required."). Report the real reason on that field instead.
 */
class RejectOversizedUploads
{
    public function handle(Request $request, Closure $next): Response
    {
        $errors = [];

        foreach ($request->allFiles() as $field => $files) {
            foreach (is_array($files) ? Arr::flatten($files) : [$files] as $file) {
                if ($file instanceof UploadedFile && in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
                    $errors[$field] = 'This file is too large to upload. The maximum is '.UploadLimits::label(PHP_INT_MAX >> 10).'.';
                }
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $next($request);
    }
}
