<?php

namespace Database\Seeders;

use App\Models\House;
use App\Models\Location;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Bhutan Dzongkhags (Districts) first.
        $this->call(LocationSeeder::class);
        $locations = Location::query()
            ->whereIn('dzongkhag_name', Location::dzongkhags())
            ->get()
            ->keyBy('dzongkhag_name');

        // Seed Users
        $admin = User::create([
            'name'     => 'Admin HRS',
            'email'    => 'admin@hrsbhutan.bt',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'phone'    => '+975 2 321000',
            'status'   => 'approved',
        ]);

        $owner1 = User::create([
            'name'     => 'Dorji Wangchuk',
            'email'    => 'dorji@example.bt',
            'password' => Hash::make('password'),
            'role'     => 'owner',
            'phone'    => '+975 17123456',
            'status'   => 'approved',
        ]);

        $owner2 = User::create([
            'name'     => 'Tshering Dema',
            'email'    => 'tshering@example.bt',
            'password' => Hash::make('password'),
            'role'     => 'owner',
            'phone'    => '+975 17654321',
            'status'   => 'approved',
        ]);

        $tenant1 = User::create([
            'name'     => 'Karma Tenzin',
            'email'    => 'karma@example.bt',
            'password' => Hash::make('password'),
            'role'     => 'tenant',
            'phone'    => '+975 17999888',
            'status'   => 'approved',
        ]);

        // Pending user example
        User::create([
            'name'     => 'Pema Dorji',
            'email'    => 'pema@example.bt',
            'password' => Hash::make('password'),
            'role'     => 'tenant',
            'phone'    => '+975 17111222',
            'status'   => 'pending',
        ]);

        User::create([
            'name'     => 'Sonam Choden',
            'email'    => 'sonam@example.bt',
            'password' => Hash::make('password'),
            'role'     => 'owner',
            'phone'    => '+975 17333444',
            'status'   => 'pending',
        ]);

        // Seed Sample Houses
        $sampleHouses = [
            [
                'owner_id'    => $owner1->id,
                'location_id' => $locations['Thimphu']->id,
                'title'       => 'Modern 2BHK near Tashichho Dzong',
                'location'    => 'Motithang, Thimphu',
                'type'        => '2BHK',
                'price'       => 12000,
                'bedrooms'    => 2,
                'bathrooms'   => 2,
                'area'        => '850 sq.ft',
                'address'     => 'Upper Motithang, Thimphu',
                'description' => 'Spacious and well-maintained 2BHK apartment with mountain views. Close to city center, schools, and markets.',
                'status'      => 'available',
                'is_featured' => true,
            ],
            [
                'owner_id'    => $owner1->id,
                'location_id' => $locations['Paro']->id,
                'title'       => 'Cozy 1BHK Studio near Paro Airport',
                'location'    => 'Bondey, Paro',
                'type'        => '1BHK',
                'price'       => 7500,
                'bedrooms'    => 1,
                'bathrooms'   => 1,
                'area'        => '450 sq.ft',
                'address'     => 'Bondey Village, Paro',
                'description' => 'Quiet and affordable studio apartment in Bondey. Perfect for single professional or couple.',
                'status'      => 'available',
                'is_featured' => true,
            ],
            [
                'owner_id'    => $owner2->id,
                'location_id' => $locations['Thimphu']->id,
                'title'       => 'Spacious 3BHK Family Home in Babesa',
                'location'    => 'Babesa, Thimphu',
                'type'        => '3BHK',
                'price'       => 18000,
                'bedrooms'    => 3,
                'bathrooms'   => 2,
                'area'        => '1200 sq.ft',
                'address'     => 'Babesa, South Thimphu',
                'description' => 'Perfect family home with 3 bedrooms, large living room, and a garden. Near expressway and schools.',
                'status'      => 'available',
                'is_featured' => true,
            ],
            [
                'owner_id'    => $owner2->id,
                'location_id' => $locations['Punakha']->id,
                'title'       => 'Riverside Apartment with Dzong View',
                'location'    => 'Khuruthang, Punakha',
                'type'        => 'Apartment',
                'price'       => 9000,
                'bedrooms'    => 2,
                'bathrooms'   => 1,
                'area'        => '700 sq.ft',
                'address'     => 'Khuruthang Town, Punakha',
                'description' => 'Beautiful apartment with view of Punakha Dzong and Mo Chhu river. Serene and peaceful location.',
                'status'      => 'available',
                'is_featured' => true,
            ],
            [
                'owner_id'    => $owner1->id,
                'location_id' => $locations['Wangdue Phodrang']->id,
                'title'       => 'Budget 1BHK in Wangdue Town',
                'location'    => 'Bajo, Wangdue',
                'type'        => '1BHK',
                'price'       => 5500,
                'bedrooms'    => 1,
                'bathrooms'   => 1,
                'area'        => '380 sq.ft',
                'address'     => 'Bajo, Wangdue Phodrang',
                'description' => 'Economical 1BHK unit in central Bajo. Ideal for students and working professionals.',
                'status'      => 'available',
                'is_featured' => true,
            ],
            [
                'owner_id'    => $owner2->id,
                'location_id' => $locations['Thimphu']->id,
                'title'       => 'Luxury Villa in Chang Bangdu',
                'location'    => 'Chang Bangdu, Thimphu',
                'type'        => 'Villa',
                'price'       => 35000,
                'bedrooms'    => 4,
                'bathrooms'   => 3,
                'area'        => '2200 sq.ft',
                'address'     => 'Chang Bangdu, Thimphu',
                'description' => 'Executive luxury villa with 4 bedrooms, modern kitchen, parking for 3 cars, and panoramic valley views.',
                'status'      => 'available',
                'is_featured' => true,
            ],
        ];

        $createdHouses = [];
        foreach ($sampleHouses as $houseData) {
            $createdHouses[] = House::create($houseData);
        }

        // ── Seed Rentals ──────────────────────────────────────────────
        $rental1 = Rental::create([
            'house_id'     => $createdHouses[0]->id,
            'tenant_id'    => $tenant1->id,
            'rental_date'  => '2025-02-01',
            'end_date'     => '2026-01-31',
            'monthly_rent' => $createdHouses[0]->price,
            'status'       => 'active',
            'notes'        => 'Annual lease, paid monthly.',
        ]);

        $rental2 = Rental::create([
            'house_id'     => $createdHouses[2]->id,
            'tenant_id'    => $tenant1->id,
            'rental_date'  => '2024-07-01',
            'end_date'     => '2025-06-30',
            'monthly_rent' => $createdHouses[2]->price,
            'status'       => 'expired',
            'notes'        => 'Previous rental — expired.',
        ]);

        // ── Seed Payments ─────────────────────────────────────────────
        // Payments for rental1 (6 months paid)
        $months1 = [
            ['2025-02-28', 'February 2025 rent'],
            ['2025-03-31', 'March 2025 rent'],
            ['2025-04-30', 'April 2025 rent'],
            ['2025-05-31', 'May 2025 rent'],
            ['2025-06-30', 'June 2025 rent'],
            ['2025-07-31', 'July 2025 rent'],
        ];
        foreach ($months1 as [$date, $note]) {
            $amt = $createdHouses[0]->price;
            Payment::create([
                'tenant_id'        => $tenant1->id,
                'rental_id'        => $rental1->id,
                'amount'           => $amt,
                'payment_date'     => $date,
                'status'           => 'paid',
                'notes'            => $note,
                'commission_rate'  => 10,
                'commission_amount'=> round($amt * 0.10, 2),
            ]);
        }

        // Payments for rental2 (12 months paid)
        for ($m = 7; $m <= 12; $m++) {
            $lastDay = date('Y-m-t', strtotime("2024-{$m}-01"));
            $amt = $createdHouses[2]->price;
            Payment::create([
                'tenant_id'        => $tenant1->id,
                'rental_id'        => $rental2->id,
                'amount'           => $amt,
                'payment_date'     => $lastDay,
                'status'           => 'paid',
                'notes'            => date('F Y', strtotime("2024-{$m}-01")) . ' rent',
                'commission_rate'  => 10,
                'commission_amount'=> round($amt * 0.10, 2),
            ]);
        }
        for ($m = 1; $m <= 6; $m++) {
            $lastDay = date('Y-m-t', strtotime("2025-{$m}-01"));
            $amt = $createdHouses[2]->price;
            Payment::create([
                'tenant_id'        => $tenant1->id,
                'rental_id'        => $rental2->id,
                'amount'           => $amt,
                'payment_date'     => $lastDay,
                'status'           => 'paid',
                'notes'            => date('F Y', strtotime("2025-{$m}-01")) . ' rent',
                'commission_rate'  => 10,
                'commission_amount'=> round($amt * 0.10, 2),
            ]);
        }
    }
}
