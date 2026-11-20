<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SeoExecutiveSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'seo@devbhoominaturals.com'],
            [
                'name' => 'SEO Executive',
                'password' => Hash::make('SeoExec@2026'),
                'role' => User::ROLE_SEO,
                'account_status' => User::ACCOUNT_ACTIVE,
                'email_verified_at' => now(),
            ]
        );
    }
}
