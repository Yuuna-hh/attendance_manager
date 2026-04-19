<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 一般ユーザー
        User::create([
            'name' => '佐藤 花子',
            'email' => 'general1@test.com',
            'password' => bcrypt('12345678'),
            'role' => 'general',
        ]);

        User::create([
            'name' => '鈴木 太郎',
            'email' => 'general2@test.com',
            'password' => bcrypt('12345678'),
            'role' => 'general',
        ]);

        User::create([
            'name' => '高橋 美咲',
            'email' => 'general3@test.com',
            'password' => bcrypt('12345678'),
            'role' => 'general',
        ]);

        // 管理者
        User::create([
            'name' => '管理者',
            'email' => 'admin@test.com',
            'password' => bcrypt('12345678'),
            'role' => 'admin',
        ]);
    }
}