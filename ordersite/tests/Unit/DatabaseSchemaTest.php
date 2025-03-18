<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin table schema.
     */
    public function test_admin_table_has_expected_columns()
    {
        $this->assertTrue(Schema::hasTable('admins'));

        $this->assertTrue(Schema::hasColumns('admins', [
            'id',
            'admin_name',
            'login_id',
            'login_password',
            'mail',
            'created_at',
            'updated_at'
        ]));
    }

    /**
     * Test store table schema.
     */
    public function test_store_table_has_expected_columns()
    {
        $this->assertTrue(Schema::hasTable('stores'));

        $this->assertTrue(Schema::hasColumns('stores', [
            'id',
            'store_id',
            'prefectures',
            'store_name',
            'login_id',
            'login_password',
            'admin',
            'created_at',
            'updated_at'
        ]));
    }

    /**
     * Test schedule table schema.
     */
    public function test_schedule_table_has_expected_columns()
    {
        $this->assertTrue(Schema::hasTable('schedules'));

        $this->assertTrue(Schema::hasColumns('schedules', [
            'id',
            'schedule_id',
            'schedule_name',
            'p_total_number',
            'created_at',
            'updated_at'
        ]));
    }

    /**
     * Test order table schema.
     */
    public function test_order_table_has_expected_columns()
    {
        $this->assertTrue(Schema::hasTable('orders'));

        $this->assertTrue(Schema::hasColumns('orders', [
            'id',
            'store_id',
            'schedule_id',
            'schedule_name',
            'p_quantity',
            'delivery_date',
            'vehicle',
            'comment',
            'created_at',
            'updated_at'
        ]));
    }

    /**
     * Test unique constraints
     */
    public function test_unique_constraints()
    {
        // Test admin login_id uniqueness
        $admin1 = \App\Models\Admin::create([
            'admin_name' => 'Admin 1',
            'login_id' => 'unique_admin',
            'login_password' => bcrypt('password'),
            'mail' => 'admin1@test.com',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        $admin2 = \App\Models\Admin::create([
            'admin_name' => 'Admin 2',
            'login_id' => 'unique_admin', // Duplicate
            'login_password' => bcrypt('password'),
            'mail' => 'admin2@test.com',
        ]);
    }

    /**
     * Test foreign key constraints
     */
    public function test_foreign_key_constraints()
    {
        // Create a store and schedule
        $store = \App\Models\Store::create([
            'store_id' => 5001,
            'prefectures' => 'Test Prefecture',
            'store_name' => 'Test Store',
            'login_id' => 'test_store',
            'login_password' => bcrypt('password'),
            'admin' => false,
        ]);

        $schedule = \App\Models\Schedule::create([
            'schedule_id' => 'TEST-SCH-001',
            'schedule_name' => 'Test Schedule',
            'p_total_number' => 500,
        ]);

        // Create an order with valid foreign keys
        $order = \App\Models\Order::create([
            'store_id' => $store->store_id,
            'schedule_id' => $schedule->schedule_id,
            'schedule_name' => $schedule->schedule_name,
            'p_quantity' => 100,
            'delivery_date' => now()->addDays(5),
            'vehicle' => '2t車',
            'comment' => 'Test order',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'store_id' => $store->store_id,
            'schedule_id' => $schedule->schedule_id,
        ]);

        // Test cascade on delete
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // Try to delete a referenced store (should fail due to foreign key constraint)
        $store->delete();
    }

    /**
     * Test nullable columns
     */
    public function test_nullable_columns()
    {
        $store = \App\Models\Store::create([
            'store_id' => 5002,
            'prefectures' => 'Test Prefecture',
            'store_name' => 'Test Store',
            'login_id' => 'test_store2',
            'login_password' => bcrypt('password'),
            'admin' => false,
        ]);

        $schedule = \App\Models\Schedule::create([
            'schedule_id' => 'TEST-SCH-002',
            'schedule_name' => 'Test Schedule 2',
            'p_total_number' => 500,
        ]);

        // Test nullable vehicle and comment
        $order = \App\Models\Order::create([
            'store_id' => $store->store_id,
            'schedule_id' => $schedule->schedule_id,
            'schedule_name' => $schedule->schedule_name,
            'p_quantity' => 100,
            'delivery_date' => now()->addDays(5),
            'vehicle' => null,
            'comment' => null,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'vehicle' => null,
            'comment' => null,
        ]);
    }
} 