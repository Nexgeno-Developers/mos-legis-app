<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\CompanyMeta;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed a single company with professional dummy data
        $company = Company::firstOrCreate(
            ['email' => 'info@example.com'],
            [
                'name' => '{Company}',
                'phone' => '02134567890',
                'address' => 'Head Office, Mumbai, India',
                'website' => 'https://example.com',
                'google_map' => '',
                'is_active' => 1,
            ],
        );

        // Optional: a single meta record for the company
        CompanyMeta::firstOrCreate(
            [
                'company_id' => $company->id,
                'meta_key' => 'support_email',
            ],
            [
                'meta_value' => 'support@example.com',
            ],
        );
    }
}
