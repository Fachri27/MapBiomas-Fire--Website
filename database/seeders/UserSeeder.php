<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Akun untuk login CMS (/cms/login). Password dapat dioverride
     * lewat variabel lingkungan ADMIN_EMAIL / ADMIN_PASSWORD.
     */
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => env('ADMIN_EMAIL', 'admin@mapbiomasfire.id')],
            [
                'name' => 'Admin MapBiomas Fire',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
                'role_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }
}
