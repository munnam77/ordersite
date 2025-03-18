<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Schedule;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected $schedule;
    protected $store;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a test schedule
        $this->schedule = Schedule::create([
            'schedule_id' => 999,
            'schedule_name' => 'テストスケジュール',
            'p_total_number' => 100,
        ]);

        // Create a test store
        $this->store = Store::create([
            'store_id' => 999,
            'prefectures' => 'テスト県',
            'store_name' => 'テスト店舗',
            'login_id' => 'store_test',
            'login_password' => 'password',
            'admin' => false,
        ]);
    }

    /**
     * Test schedule fillable attributes.
     */
    public function test_fillable_attributes(): void
    {
        $this->assertEquals([
            'schedule_id',
            'schedule_name',
            'p_total_number',
        ], $this->schedule->getFillable());
    }

    /**
     * Test schedule has many orders.
     */
    public function test_schedule_has_many_orders(): void
    {
        // Create some orders for the test schedule
        Order::create([
            'store_id' => $this->store->id,
            'schedule_id' => $this->schedule->id,
            'schedule_name' => $this->schedule->schedule_name,
            'p_quantity' => 10,
        ]);

        Order::create([
            'store_id' => $this->store->id,
            'schedule_id' => $this->schedule->id,
            'schedule_name' => $this->schedule->schedule_name,
            'p_quantity' => 20,
        ]);

        // Refresh the model from database
        $this->schedule->refresh();

        // Check relationship
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $this->schedule->orders);
        $this->assertCount(2, $this->schedule->orders);
    }

    /**
     * Test total ordered quantity attribute.
     */
    public function test_total_ordered_quantity_attribute(): void
    {
        // Create some orders for the test schedule
        Order::create([
            'store_id' => $this->store->id,
            'schedule_id' => $this->schedule->id,
            'schedule_name' => $this->schedule->schedule_name,
            'p_quantity' => 15,
        ]);

        Order::create([
            'store_id' => $this->store->id,
            'schedule_id' => $this->schedule->id,
            'schedule_name' => $this->schedule->schedule_name,
            'p_quantity' => 25,
        ]);

        // Refresh the model from database
        $this->schedule->refresh();

        // Check total ordered quantity
        $this->assertEquals(40, $this->schedule->total_ordered_quantity);
    }

    /**
     * Test remaining quantity attribute.
     */
    public function test_remaining_quantity_attribute(): void
    {
        // Create some orders for the test schedule
        Order::create([
            'store_id' => $this->store->id,
            'schedule_id' => $this->schedule->id,
            'schedule_name' => $this->schedule->schedule_name,
            'p_quantity' => 30,
        ]);

        Order::create([
            'store_id' => $this->store->id,
            'schedule_id' => $this->schedule->id,
            'schedule_name' => $this->schedule->schedule_name,
            'p_quantity' => 20,
        ]);

        // Refresh the model from database
        $this->schedule->refresh();

        // Check remaining quantity
        $this->assertEquals(50, $this->schedule->remaining_quantity); // 100 - (30 + 20)
    }
} 