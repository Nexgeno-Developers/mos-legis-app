<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Image uploads from the admin's full text editor (pages and blogs). Responds in the editor's
 * (Jodit) upload format; errors are shown in the editor.
 */
class EditorImageController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->can('pages.edit') || $user->can('pages.create') || $user->can('blogs.create') || $user->can('blogs.edit'), 403);

        $validator = Validator::make($request->all(), [
            'files' => ['required', 'array', 'max:10'],
            'files.*' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:4096'],
        ], [
            'files.*.image' => 'Only image files can be uploaded.',
            'files.*.mimes' => 'Use a JPG, PNG, GIF or WebP image.',
            'files.*.max' => 'Each image must be 4 MB or smaller.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'data' => ['messages' => $validator->errors()->all(), 'files' => []]]);
        }

        $folder = 'editor/'.now()->format('Y/m');
        $files = collect($request->file('files'))->map(fn ($file) => basename($file->store($folder, 'public')))->values();

        return response()->json(['success' => true, 'data' => [
            'baseurl' => Storage::disk('public')->url($folder).'/',
            'files' => $files->all(),
            'isImages' => $files->map(fn () => true)->all(),
            'messages' => [],
        ]]);
    }
}
