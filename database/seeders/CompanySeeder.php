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
            ['email' => 'info@ctrlshift.co.in'],
            [
                'name' => 'CtrlShift Co-Working Space',
                'phone' => '+919004936299',
                'address' => 'Unit 201-b, Kohinoor City Mall Ground Floor, A Wing Commercial Bldg Kirol Road, Kurla West, Mumbai 400070.',
                'website' => 'https://ctrlshift.co.in',
                'google_map' => '',
                'is_active' => 1,
            ],
        );

        $metaDefaults = [
            'support_email' => 'support@example.com',
            'bank_account_holder_name' => 'WorkNest Spaces Pvt Ltd',
            'bank_name' => 'HDFC Bank',
            'bank_account_number' => '50100123456789',
            'bank_ifsc' => 'HDFC0001234',
            'bank_branch' => 'Indiranagar Branch',
        ];

        foreach ($metaDefaults as $metaKey => $metaValue) {
            CompanyMeta::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'meta_key' => $metaKey,
                ],
                [
                    'meta_value' => $metaValue,
                ],
            );
        }
    }
}
