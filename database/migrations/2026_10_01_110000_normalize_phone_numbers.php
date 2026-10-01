<?php

use App\Support\PhoneNumbers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Phone numbers are now stored in international E.164 format (+919876543210).
 * Convert existing values that are valid; anything unparseable is left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'addresses', 'enquiries'] as $table) {
            DB::table($table)->whereNotNull('phone')->where('phone', '!=', '')->orderBy('id')
                ->chunkById(500, function ($rows) use ($table) {
                    foreach ($rows as $row) {
                        $normalized = PhoneNumbers::normalize($row->phone);

                        if (is_string($normalized) && $normalized !== $row->phone && str_starts_with($normalized, '+')) {
                            DB::table($table)->where('id', $row->id)->update(['phone' => $normalized]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        // Normalised numbers remain valid; nothing to undo.
    }
};
