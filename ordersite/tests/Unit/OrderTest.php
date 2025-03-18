<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Store;
use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected $order;
    protected $store;
    protected $schedule;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a test store
        $this->store = Store::create([
            'store_id' => 999,
            'prefectures' => 'テスト県',
            'store_name' => 'テスト店舗',
            'login_id' => 'store_test',
            'login_password' => 'password',
            'admin' => false,
        ]);

        // Create a test schedule
        $this->schedule = Schedule::create([
            'schedule_id' => 999,
            'schedule_name' => 'テストスケジュール',
            'p_total_number' => 100,
        ]);

        // Create a test order
        $this->order = Order::create([
            'store_id' => $this->store->id,
            'schedule_id' => $this->schedule->id,
            'schedule_name' => $this->schedule->schedule_name,
            'p_quantity' => 25,
            'delivery_date' => now()->addDays(7),
            'vehicle' => 'テスト車両',
            'comment' => 'テストコメント',
        ]);
    }

    /**
     * Test order fillable attributes.
     */
    public function test_fillable_attributes(): void
    {
        $this->assertEquals([
            'store_id',
            'schedule_id',
            'schedule_name',
            'p_quantity',
            'comment',
            'delivery_date',
            'vehicle',
            'working_day',
            'working_time',
        ], $this->order->getFillable());
    }

    /**
     * Test order belongs to store.
     */
    public function test_order_belongs_to_store(): void
    {
        $this->assertInstanceOf('App\Models\Store', $this->order->store);
        $this->assertEquals($this->store->id, $this->order->store->id);
        $this->assertEquals('テスト店舗', $this->order->store->store_name);
    }

    /**
     * Test order belongs to schedule.
     */
    public function test_order_belongs_to_schedule(): void
    {
        $this->assertInstanceOf('App\Models\Schedule', $this->order->schedule);
        $this->assertEquals($this->schedule->id, $this->order->schedule->id);
        $this->assertEquals('テストスケジュール', $this->order->schedule->schedule_name);
    }

    /**
     * Test date casting.
     */
    public function test_date_casting(): void
    {
        $this->assertInstanceOf('Illuminate\Support\Carbon', $this->order->delivery_date);
        $this->assertInstanceOf('Illuminate\Support\Carbon', $this->order->created_at);
        $this->assertInstanceOf('Illuminate\Support\Carbon', $this->order->updated_at);
    }

    /**
     * Test quantity is correctly stored and cast.
     */
    public function test_quantity_attribute(): void
    {
        $this->assertEquals(25, $this->order->p_quantity);
        $this->assertIsFloat($this->order->p_quantity);
    }

    /**
     * Test order can be created with minimum required fields.
     */
    public function test_order_creation_with_minimum_fields(): void
    {
        $order = Order::create([
            'store_id' => $this->store->id,
            'schedule_id' => $this->schedule->id,
            'schedule_name' => $this->schedule->schedule_name,
            'p_quantity' => 10,
        ]);

        $this->assertInstanceOf('App\Models\Order', $order);
        $this->assertEquals(10, $order->p_quantity);
        $this->assertNull($order->comment);
        $this->assertNull($order->delivery_date);
        $this->assertNull($order->vehicle);
    }

    /**
     * Test creating order updates schedule's ordered quantity.
     */
    public function test_order_updates_schedule_ordered_quantity(): void
    {
        // Refresh the schedule
        $this->schedule->refresh();
        
        // Initial order has quantity 25
        $this->assertEquals(25, $this->schedule->total_ordered_quantity);
        $this->assertEquals(75, $this->schedule->remaining_quantity);

        // Add another order
        Order::create([
            'store_id' => $this->store->id,
            'schedule_id' => $this->schedule->id,
            'schedule_name' => $this->schedule->schedule_name,
            'p_quantity' => 15,
        ]);

        // Refresh the schedule
        $this->schedule->refresh();
        
        // Check totals are updated
        $this->assertEquals(40, $this->schedule->total_ordered_quantity);
        $this->assertEquals(60, $this->schedule->remaining_quantity);
    }
} 