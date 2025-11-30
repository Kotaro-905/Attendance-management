<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class StaffDummySeeder extends Seeder
{
    public function run(): void
    {
        $staffs = [
            ['name' => '山田 太郎', 'email' => 'staff1@example.com'],
            ['name' => '西 怜奈',  'email' => 'staff2@example.com'],
            ['name' => '増田 一世', 'email' => 'staff3@example.com'],
            ['name' => '山本 敬吉', 'email' => 'staff4@example.com'],
            ['name' => '秋田 朋美', 'email' => 'staff5@example.com'],
            ['name' => '中西 教夫', 'email' => 'staff6@example.com'],
        ];

        foreach ($staffs as $staff) {
            User::updateOrCreate(
                ['email' => $staff['email']],
                [
                    'name'              => $staff['name'],
                    'password'          => Hash::make('password'),
                    'role'              => User::ROLE_GENERAL, // 一般ユーザー
                    'email_verified_at' => Carbon::now(),      // ★ここで認証済みにする
                ]
            );
        }
    }
}
