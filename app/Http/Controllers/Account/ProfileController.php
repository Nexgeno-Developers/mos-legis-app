<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\AddressRequest;
use App\Http\Requests\Account\ProfileRequest;
use App\Models\AuthorCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * SOW B.02 — author profile (clarification #4) and billing address (clarification #5).
 */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('account.profile', [
            'user' => $request->user()->load(['authorProfile', 'address', 'socialAccounts']),
            'authorCategories' => AuthorCategory::active()->orderBy('name')->pluck('name', 'id'),
            'countries' => config('countries'),
        ]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($request, $user) {
            $user->update($request->safe()->only(['name', 'phone']) + ($request->filled('password') ? ['password' => $request->validated('password')] : []));

            $profile = $request->safe()->only(['author_category_id', 'institution', 'country', 'bio']);
            if ($request->hasFile('profile_picture')) {
                if ($old = $user->authorProfile?->profile_picture) {
                    Storage::disk('public')->delete($old);
                }
                $profile['profile_picture'] = $request->file('profile_picture')->store('profiles', 'public');
            }
            $user->authorProfile()->updateOrCreate([], $profile);
        });

        $next = (string) $request->input('next');

        // Only same-site paths, e.g. back to the submission form.
        return str_starts_with($next, '/') && ! str_starts_with($next, '//')
            ? redirect($next)->with('success', 'Profile saved — you can continue your submission.')
            : back()->with('success', 'Profile saved.');
    }

    public function updateAddress(AddressRequest $request): RedirectResponse
    {
        $request->user()->address()->updateOrCreate([], $request->validated());

        return back()->with('success', 'Billing address saved.');
    }
}
