<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin
        DB::table('admins')->insert([
            'admin_name' => '管理者',
            'login_id' => 'admin1',
            'login_password' => Hash::make('password1'),
            'mail' => 'admin@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Stores
        $stores = [
            [
                'store_id' => 1, 
                'prefectures' => '大阪', 
                'store_name' => '泉北', 
                'login_id' => '1', 
                'login_password' => Hash::make('001_abc'), 
                'admin' => 0
            ],
            [
                'store_id' => 2, 
                'prefectures' => '大阪', 
                'store_name' => '鳳', 
                'login_id' => '2', 
                'login_password' => Hash::make('002_abc'), 
                'admin' => 0
            ],
            [
                'store_id' => 3, 
                'prefectures' => '東京', 
                'store_name' => '新宿', 
                'login_id' => '3', 
                'login_password' => Hash::make('003_abc'), 
                'admin' => 0
            ],
            [
                'store_id' => 4, 
                'prefectures' => '東京', 
                'store_name' => '渋谷', 
                'login_id' => '4', 
                'login_password' => Hash::make('004_abc'), 
                'admin' => 0
            ],
            [
                'store_id' => 5, 
                'prefectures' => '北海道', 
                'store_name' => '札幌', 
                'login_id' => '5', 
                'login_password' => Hash::make('005_abc'), 
                'admin' => 0
            ],
            [
                'store_id' => 6, 
                'prefectures' => '北海道', 
                'store_name' => '函館', 
                'login_id' => '6', 
                'login_password' => Hash::make('006_abc'), 
                'admin' => 0
            ],
            [
                'store_id' => 7, 
                'prefectures' => '福岡', 
                'store_name' => '博多', 
                'login_id' => '7', 
                'login_password' => Hash::make('007_abc'), 
                'admin' => 0
            ],
            [
                'store_id' => 8, 
                'prefectures' => '福岡', 
                'store_name' => '小倉', 
                'login_id' => '8', 
                'login_password' => Hash::make('008_abc'), 
                'admin' => 0
            ],
            [
                'store_id' => 9, 
                'prefectures' => '沖縄', 
                'store_name' => '那覇', 
                'login_id' => '9', 
                'login_password' => Hash::make('009_abc'), 
                'admin' => 0
            ],
            [
                'store_id' => 10, 
                'prefectures' => '沖縄', 
                'store_name' => '石垣', 
                'login_id' => '10', 
                'login_password' => Hash::make('010_abc'), 
                'admin' => 0
            ],
        ];

        foreach ($stores as $store) {
            $store['created_at'] = now();
            $store['updated_at'] = now();
            DB::table('stores')->insert($store);
        }

        // Create Schedules
        $schedules = [
            ['schedule_id' => 1, 'schedule_name' => '4/7週', 'p_total_number' => 100],
            ['schedule_id' => 2, 'schedule_name' => '4/14週', 'p_total_number' => 120],
            ['schedule_id' => 3, 'schedule_name' => '4/21週', 'p_total_number' => 130],
            ['schedule_id' => 4, 'schedule_name' => '4/28週', 'p_total_number' => 140],
            ['schedule_id' => 5, 'schedule_name' => '5/5週', 'p_total_number' => 150],
            ['schedule_id' => 6, 'schedule_name' => '5/12週', 'p_total_number' => 160],
            ['schedule_id' => 7, 'schedule_name' => '5/19週', 'p_total_number' => 170],
        ];

        foreach ($schedules as $schedule) {
            $schedule['created_at'] = now();
            $schedule['updated_at'] = now();
            DB::table('schedules')->insert($schedule);
        }

        // Create some sample orders
        $sampleOrders = [
            [
                'store_id' => 1,
                'schedule_id' => 1,
                'schedule_name' => '4/7週',
                'p_quantity' => 6,
                'comment' => 'よろしくお願いします',
                'delivery_date' => '2025-04-09',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'store_id' => 2,
                'schedule_id' => 1,
                'schedule_name' => '4/7週',
                'p_quantity' => 8,
                'comment' => '配送時間は午前中希望です',
                'delivery_date' => '2025-04-10',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'store_id' => 3,
                'schedule_id' => 2,
                'schedule_name' => '4/14週',
                'p_quantity' => 10,
                'comment' => null,
                'delivery_date' => '2025-04-16',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($sampleOrders as $order) {
            DB::table('orders')->insert($order);
        }
    }
} 