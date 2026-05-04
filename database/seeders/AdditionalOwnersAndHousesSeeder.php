<?php

namespace Database\Seeders;

use App\Models\House;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdditionalOwnersAndHousesSeeder extends Seeder
{
    public function run(): void
    {
        $locations = Location::orderedDzongkhags()->keyBy('dzongkhag_name');

        $owners = [
            [
                'name' => 'Kinzang Dorji',
                'email' => 'kinzang.owner@hrsbt.test',
                'phone' => '+97517100011',
            ],
            [
                'name' => 'Dechen Wangmo',
                'email' => 'dechen.owner@hrsbt.test',
                'phone' => '+97517100012',
            ],
            [
                'name' => 'Tashi Namgyel',
                'email' => 'tashi.owner@hrsbt.test',
                'phone' => '+97517100013',
            ],
        ];

        $ownerIdsByEmail = [];
        foreach ($owners as $owner) {
            $user = User::firstOrCreate(
                ['email' => $owner['email']],
                [
                    'name' => $owner['name'],
                    'password' => Hash::make('password'),
                    'role' => 'owner',
                    'phone' => $owner['phone'],
                    'status' => 'approved',
                ]
            );

            if (! $user->wasRecentlyCreated) {
                $user->update([
                    'role' => 'owner',
                    'status' => 'approved',
                    'phone' => $owner['phone'],
                    'password' => Hash::make('password'),
                ]);
            }

            $ownerIdsByEmail[$owner['email']] = $user->id;
        }

        $houses = [
            [
                'owner_email' => 'kinzang.owner@hrsbt.test',
                'dzongkhag' => 'Thimphu',
                'title' => 'Sunny 2BHK in Changzamtog',
                'location' => 'Changzamtog, Thimphu',
                'type' => '2BHK',
                'price' => 13500,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'area' => '900 sq.ft',
                'address' => 'Changzamtog, Thimphu',
                'description' => 'Bright 2BHK with balcony and parking.',
            ],
            [
                'owner_email' => 'dechen.owner@hrsbt.test',
                'dzongkhag' => 'Punakha',
                'title' => 'Family 3BHK with Garden',
                'location' => 'Khuruthang, Punakha',
                'type' => '3BHK',
                'price' => 16500,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area' => '1300 sq.ft',
                'address' => 'Khuruthang, Punakha',
                'description' => 'Spacious family home with private garden.',
            ],
            [
                'owner_email' => 'tashi.owner@hrsbt.test',
                'dzongkhag' => 'Sarpang',
                'title' => 'Duplex Home near Gelephu',
                'location' => 'Gelephu, Sarpang',
                'type' => 'Duplex',
                'price' => 21000,
                'bedrooms' => 3,
                'bathrooms' => 3,
                'area' => '1700 sq.ft',
                'address' => 'Gelephu, Sarpang',
                'description' => 'Large duplex suitable for bigger families.',
            ],
        ];

        // Remove older extra sample houses from this same seed set so only 3 remain.
        House::query()
            ->whereIn('owner_id', array_values($ownerIdsByEmail))
            ->whereIn('title', [
                'Modern Studio near Town Center',
                'Affordable 1BHK in Bajo',
                'Cozy Apartment in Trashigang',
            ])
            ->delete();

        foreach ($houses as $houseData) {
            $location = $locations->get($houseData['dzongkhag']);
            $ownerId = $ownerIdsByEmail[$houseData['owner_email']] ?? null;

            if (! $location || ! $ownerId) {
                continue;
            }

            House::firstOrCreate(
                [
                    'owner_id' => $ownerId,
                    'title' => $houseData['title'],
                ],
                [
                    'location_id' => $location->id,
                    'location' => $houseData['location'],
                    'type' => $houseData['type'],
                    'price' => $houseData['price'],
                    'description' => $houseData['description'],
                    'image' => null,
                    'bedrooms' => $houseData['bedrooms'],
                    'bathrooms' => $houseData['bathrooms'],
                    'area' => $houseData['area'],
                    'address' => $houseData['address'],
                    'status' => 'available',
                    'is_featured' => false,
                ]
            );
        }
    }
}
