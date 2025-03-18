<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Store;
use App\Models\Schedule;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;

class StoreDashboardTest extends TestCase
{
    use RefreshDatabase;

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
            'store_id' => 1001,
            'prefectures' => '東京都',
            'store_name' => 'テスト東京店',
            'login_id' => 'tokyo_store',
            'login_password' => bcrypt('password'),
            'admin' => false,
        ]);

        // Create a test schedule
        $this->schedule = Schedule::create([
            'schedule_id' => 'SCH-TEST-001',
            'schedule_name' => 'テストスケジュール１',
            'p_total_number' => 1000,
        ]);
    }

    /**
     * Test store dashboard page loads correctly.
     */
    public function test_store_dashboard_loads(): void
    {
        $this->actingAs($this->store, 'store');
        
        $response = $this->get('/store/dashboard');
        
        $response->assertStatus(200);
        $response->assertViewIs('store.dashboard');
        $response->assertSee('発注入力');
        $response->assertSee('注文履歴');
        $response->assertSee('スケジュール選択');
    }

    /**
     * Test store can see available schedules.
     */
    public function test_store_can_see_available_schedules(): void
    {
        $this->actingAs($this->store, 'store');
        
        // Create an additional schedule
        Schedule::create([
            'schedule_id' => 'SCH-TEST-002',
            'schedule_name' => 'テストスケジュール２',
            'p_total_number' => 500,
        ]);
        
        $response = $this->get('/store/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('テストスケジュール１');
        $response->assertSee('テストスケジュール２');
    }

    /**
     * Test store can submit a valid order.
     */
    public function test_store_can_submit_valid_order(): void
    {
        $this->actingAs($this->store, 'store');
        
        $orderData = [
            'schedule_id' => 'SCH-TEST-001',
            'p_quantity' => 200,
            'delivery_date' => now()->addDays(10)->format('Y-m-d'),
            'vehicle' => '4t車',
            'comment' => 'テスト注文です。',
        ];
        
        $response = $this->post('/store/orders', $orderData);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Check that the order was created in the database
        $this->assertDatabaseHas('orders', [
            'store_id' => $this->store->store_id,
            'schedule_id' => $this->schedule->schedule_id,
            'p_quantity' => 200,
            'vehicle' => '4t車',
            'comment' => 'テスト注文です。',
        ]);
        
        // Check that the schedule's ordered quantity was updated
        $updatedSchedule = Schedule::find($this->schedule->id);
        $this->assertEquals(200, $updatedSchedule->total_ordered_quantity);
        $this->assertEquals(800, $updatedSchedule->remaining_quantity);
    }

    /**
     * Test store cannot submit order with invalid data.
     */
    public function test_store_cannot_submit_order_with_invalid_data(): void
    {
        $this->actingAs($this->store, 'store');
        
        // Missing required fields
        $response = $this->post('/store/orders', [
            'schedule_id' => '',
            'p_quantity' => '',
            'delivery_date' => '',
        ]);
        
        $response->assertSessionHasErrors(['schedule_id', 'p_quantity', 'delivery_date']);
        
        // Quantity too large
        $response = $this->post('/store/orders', [
            'schedule_id' => $this->schedule->schedule_id,
            'p_quantity' => 1500, // More than total
            'delivery_date' => now()->addDays(10)->format('Y-m-d'),
        ]);
        
        $response->assertSessionHasErrors('p_quantity');
        
        // Invalid date format
        $response = $this->post('/store/orders', [
            'schedule_id' => $this->schedule->schedule_id,
            'p_quantity' => 100,
            'delivery_date' => 'invalid-date',
        ]);
        
        $response->assertSessionHasErrors('delivery_date');
    }

    /**
     * Test store can see their order history.
     */
    public function test_store_can_see_order_history(): void
    {
        $this->actingAs($this->store, 'store');
        
        // Create some orders for this store
        $orders = [];
        for ($i = 1; $i <= 3; $i++) {
            $orders[] = Order::create([
                'store_id' => $this->store->store_id,
                'schedule_id' => $this->schedule->schedule_id,
                'schedule_name' => $this->schedule->schedule_name,
                'p_quantity' => $i * 100,
                'delivery_date' => now()->addDays($i * 2)->format('Y-m-d'),
                'vehicle' => '2t車',
                'comment' => "テスト注文 {$i}",
            ]);
        }
        
        $response = $this->get('/store/dashboard');
        
        $response->assertStatus(200);
        
        // Check for order details in the response
        foreach ($orders as $order) {
            $response->assertSee($order->schedule_name);
            $response->assertSee((string)$order->p_quantity);
            $response->assertSee($order->delivery_date->format('Y/m/d'));
        }
    }

    /**
     * Test store can only see their own orders.
     */
    public function test_store_can_only_see_their_own_orders(): void
    {
        $this->actingAs($this->store, 'store');
        
        // Create an order for the authenticated store
        $ownOrder = Order::create([
            'store_id' => $this->store->store_id,
            'schedule_id' => $this->schedule->schedule_id,
            'schedule_name' => $this->schedule->schedule_name,
            'p_quantity' => 100,
            'delivery_date' => now()->addDays(5)->format('Y-m-d'),
            'vehicle' => '2t車',
            'comment' => "自店の注文",
        ]);
        
        // Create another store and an order for it
        $otherStore = Store::create([
            'store_id' => 1002,
            'prefectures' => '大阪府',
            'store_name' => 'テスト大阪店',
            'login_id' => 'osaka_store',
            'login_password' => bcrypt('password'),
            'admin' => false,
        ]);
        
        $otherOrder = Order::create([
            'store_id' => $otherStore->store_id,
            'schedule_id' => $this->schedule->schedule_id,
            'schedule_name' => $this->schedule->schedule_name,
            'p_quantity' => 200,
            'delivery_date' => now()->addDays(7)->format('Y-m-d'),
            'vehicle' => '4t車',
            'comment' => "他店の注文",
        ]);
        
        $response = $this->get('/store/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('自店の注文');
        $response->assertSee((string)$ownOrder->p_quantity);
        
        // Should not see other store's order
        $response->assertDontSee('他店の注文');
    }

    /**
     * Test store cannot submit an order exceeding remaining quantity.
     */
    public function test_store_cannot_exceed_remaining_quantity(): void
    {
        $this->actingAs($this->store, 'store');
        
        // Create an existing order that uses 600 units
        Order::create([
            'store_id' => $this->store->store_id,
            'schedule_id' => $this->schedule->schedule_id,
            'schedule_name' => $this->schedule->schedule_name,
            'p_quantity' => 600,
            'delivery_date' => now()->addDays(5)->format('Y-m-d'),
            'vehicle' => '2t車',
            'comment' => "最初の注文",
        ]);
        
        // Refresh the schedule to update totals
        $this->schedule->refresh();
        
        // Try to order more than remaining (should be 400 left)
        $response = $this->post('/store/orders', [
            'schedule_id' => $this->schedule->schedule_id,
            'p_quantity' => 500, // More than remaining
            'delivery_date' => now()->addDays(10)->format('Y-m-d'),
            'vehicle' => '4t車',
            'comment' => '超過注文',
        ]);
        
        $response->assertSessionHasErrors('p_quantity');
        
        // Should be able to order exactly the remaining amount
        $response = $this->post('/store/orders', [
            'schedule_id' => $this->schedule->schedule_id,
            'p_quantity' => 400, // Exactly remaining
            'delivery_date' => now()->addDays(10)->format('Y-m-d'),
            'vehicle' => '4t車',
            'comment' => '残量ちょうどの注文',
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Check that the schedule is now fully ordered
        $this->schedule->refresh();
        $this->assertEquals(1000, $this->schedule->total_ordered_quantity);
        $this->assertEquals(0, $this->schedule->remaining_quantity);
    }
} 