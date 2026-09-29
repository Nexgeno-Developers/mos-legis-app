<?php

namespace App\Models;

use App\Enums\EnquiryForm;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * SOW A.19 — Contact and Career form submissions; extra fields live in form_data.
 */
#[Fillable(['form_name', 'name', 'email', 'phone', 'ip', 'form_data'])]
class Enquiry extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'form_name' => EnquiryForm::class,
            'form_data' => 'array',
        ];
    }
}
