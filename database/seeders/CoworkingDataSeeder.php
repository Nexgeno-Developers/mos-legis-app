<?php

namespace Database\Seeders;

use App\Models\Cabin;
use App\Models\Property;
use App\Models\Role;
use App\Models\Seat;
use App\Models\SeatPricing;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CoworkingDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffRole = Role::where('name', 'staff')->first();
        $customerRole = Role::where('name', Role::CUSTOMER)->first();

        if (! $staffRole || ! $customerRole) {
            $this->command?->warn('Staff or customer role not found. Run RoleSeeder first.');

            return;
        }

        $this->seedStaffUsers($staffRole);
        $this->seedCustomers($customerRole);
        $this->seedPropertyWithCabinsAndSeats();
    }

    private function seedStaffUsers(Role $staffRole): void
    {
        $staffUsers = [
            ['name' => 'Staff User One', 'email' => 'staff1@example.com'],
            ['name' => 'Staff User Two', 'email' => 'staff2@example.com'],
        ];

        foreach ($staffUsers as $staff) {
            $user = User::updateOrCreate(
                ['email' => $staff['email']],
                [
                    'role_id' => $staffRole->id,
                    'name' => $staff['name'],
                    'phone' => null,
                    'password' => Hash::make($staff['email']),
                    'is_active' => 1,
                ]
            );

            $user->syncRoles([$staffRole->name]);
        }
    }

    private function seedCustomers(Role $customerRole): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $email = "customer{$i}@example.com";

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'role_id' => $customerRole->id,
                    'name' => "Customer {$i}",
                    'phone' => '9'.str_pad((string) $i, 9, '0', STR_PAD_LEFT),
                    'password' => Hash::make(Str::random(32)),
                    'is_active' => 1,
                ]
            );

            $user->syncRoles([$customerRole->name]);

            UserDetail::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'detail_key' => 'company_name',
                ],
                [
                    'detail_value' => "Customer Company {$i}",
                ]
            );
        }
    }

    private function seedPropertyWithCabinsAndSeats(): void
    {
        $property = Property::firstOrCreate(
            ['email' => 'property@ctrlshift.co.in'],
            [
                'name' => 'CtrlShift Main Hub',
                'phone' => '+919004936200',
                'address' => 'Unit 201-b, Kohinoor City Mall, Kurla West, Mumbai 400070.',
                'facilities' => 'Wi-Fi, Meeting rooms, Pantry, Parking, 24/7 access',
                'thumbnail' => '0',
                'images' => '0',
                'status' => 1,
            ]
        );

        $cabins = [
            ['name' => 'Zone A', 'type' => 'flexible'],
            ['name' => 'Zone B', 'type' => 'flexible'],
            ['name' => 'Zone C', 'type' => 'flexible'],
        ];

        foreach ($cabins as $cabinData) {
            $cabin = Cabin::firstOrCreate(
                [
                    'property_id' => $property->id,
                    'name' => $cabinData['name'],
                ],
                [
                    'type' => $cabinData['type'],
                    'thumbnail' => '0',
                    'images' => '0',
                    'status' => 1,
                ]
            );

            if ($cabin->seats()->exists()) {
                continue;
            }

            $seatCount = random_int(1, 10);

            for ($seatIndex = 1; $seatIndex <= $seatCount; $seatIndex++) {
                $seat = Seat::create([
                    'property_id' => $property->id,
                    'cabin_id' => $cabin->id,
                    'seat_no' => strtoupper(substr($cabinData['type'], 0, 1)).$cabin->id.'-'.$seatIndex,
                    'status' => 1,
                ]);

                foreach (['monthly' => 5000 + ($seatIndex * 250), 'yearly' => 50000 + ($seatIndex * 2500)] as $duration => $price) {
                    SeatPricing::updateOrCreate(
                        [
                            'seat_id' => $seat->id,
                            'duration' => $duration,
                        ],
                        [
                            'price' => $price,
                        ]
                    );
                }
            }
        }
    }
}
