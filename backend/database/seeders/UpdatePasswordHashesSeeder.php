<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * 既存ユーザーのパスワードハッシュを新しいコストで再作成するシーダー
 * 
 * 使用方法:
 * php artisan db:seed --class=UpdatePasswordHashesSeeder
 */
class UpdatePasswordHashesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $password = 'password'; // テストユーザーのパスワード

        foreach ($users as $user) {
            // 新しいコスト（8）でパスワードハッシュを再作成
            $user->password = Hash::make($password);
            $user->save();
            
            $this->command->info("Updated password hash for user: {$user->email}");
        }

        $this->command->info("Password hash update completed. Total users: {$users->count()}");
    }
}
