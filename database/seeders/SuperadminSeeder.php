<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPERADMIN_EMAIL');
        $password = env('SUPERADMIN_PASSWORD');

        if (! $email || ! $password) {
            throw new RuntimeException(
                'SUPERADMIN_EMAIL dan SUPERADMIN_PASSWORD wajib diisi di .env sebelum menjalankan seeder ini.'
            );
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('SUPERADMIN_NAME', 'Superadmin'),
                'password' => $password,
            ]
        );
    }
}
