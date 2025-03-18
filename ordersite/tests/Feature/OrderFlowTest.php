<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Schedule;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $store;
    protected $schedule;
    
    protected function setUp(): void
    {
        parent::setUp();

        // Create a test admin
        $this->admin = Admin::create([
            'admin_name' => 'テスト管理者',
            'login_id' => 'admin_test',
            'login_password' => bcrypt('password'),
            'mail' => 'admin@test.com',
        ]);

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
     * Test the complete order flow from a store placing an order to admin verification.
     */
    public function test_complete_order_flow()
    {
        // 1. Store logs in
        $response = $this->post('/login', [
            'login_id' => 'tokyo_store',
            'password' => 'password', 
            'user_type' => 'store'
        ]);
        $response->assertRedirect('/store/dashboard');
        
        // 2. Store submits a new order
        $orderData = [
            'schedule_id' => 'SCH-TEST-001',
            'p_quantity' => 100,
            'delivery_date' => now()->addDays(7)->format('Y-m-d'),
            'vehicle' => '2t車',
            'comment' => 'テスト注文です',
        ];
        
        $this->followingRedirects();
        $response = $this->post('/store/orders', $orderData);
        $response->assertStatus(200);
        $response->assertSee('注文が正常に登録されました');
        
        // 3. Verify order was created
        $order = Order::where('store_id', $this->store->store_id)
            ->where('schedule_id', $this->schedule->schedule_id)
            ->first();
            
        $this->assertNotNull($order);
        $this->assertEquals($orderData['p_quantity'], $order->p_quantity);
        $this->assertEquals($orderData['delivery_date'], $order->delivery_date->format('Y-m-d'));
        $this->assertEquals($orderData['vehicle'], $order->vehicle);
        $this->assertEquals($orderData['comment'], $order->comment);
        
        // 4. Check that schedule quantities are updated
        $updatedSchedule = Schedule::find($this->schedule->id);
        $this->assertEquals(100, $updatedSchedule->total_ordered_quantity);
        $this->assertEquals(900, $updatedSchedule->remaining_quantity);

        // 5. Logout store
        $this->post('/logout');
        
        // 6. Admin logs in
        $response = $this->post('/login', [
            'login_id' => 'admin_test',
            'password' => 'password',
            'user_type' => 'admin'
        ]);
        $response->assertRedirect('/admin/dashboard');
        
        // 7. Admin views dashboard and sees the schedule
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('テストスケジュール１');
        $response->assertSee('100/1000');
        
        // 8. Admin views schedule details
        $response = $this->get('/admin/schedules/' . $this->schedule->id);
        $response->assertStatus(200);
        $response->assertSee('テストスケジュール１');
        $response->assertSee('テスト東京店');
        $response->assertSee('100');
        
        // 9. Admin updates schedule
        $updateData = [
            'schedule_name' => 'テストスケジュール１ - 更新済み',
            'p_total_number' => 1200,
        ];
        
        $response = $this->put('/admin/schedules/' . $this->schedule->id, $updateData);
        $response->assertRedirect('/admin/schedules/' . $this->schedule->id);
        
        // 10. Verify schedule was updated
        $updatedSchedule = Schedule::find($this->schedule->id);
        $this->assertEquals($updateData['schedule_name'], $updatedSchedule->schedule_name);
        $this->assertEquals($updateData['p_total_number'], $updatedSchedule->p_total_number);
        $this->assertEquals(100, $updatedSchedule->total_ordered_quantity);
        $this->assertEquals(1100, $updatedSchedule->remaining_quantity);
        
        // 11. Admin exports schedule to CSV
        $response = $this->get('/admin/schedules/' . $this->schedule->id . '/export');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="' . $updatedSchedule->schedule_name . '.csv"');
    }

    /**
     * Test order validation constraints.
     */
    public function test_order_validation()
    {
        // Login as store
        $this->actingAs($this->store, 'store');
        
        // Test validation errors
        $response = $this->post('/store/orders', [
            'schedule_id' => '',
            'p_quantity' => '',
            'delivery_date' => '',
        ]);
        
        $response->assertSessionHasErrors(['schedule_id', 'p_quantity', 'delivery_date']);
        
        // Test exceeding available quantity
        $response = $this->post('/store/orders', [
            'schedule_id' => $this->schedule->schedule_id,
            'p_quantity' => 2000, // More than available
            'delivery_date' => now()->addDays(7)->format('Y-m-d'),
        ]);
        
        $response->assertSessionHasErrors(['p_quantity']);
    }

    /**
     * Test store can view their order history.
     */
    public function test_store_can_view_order_history()
    {
        // Create a few orders for this store
        $this->actingAs($this->store, 'store');
        
        // Create 3 orders
        for ($i = 1; $i <= 3; $i++) {
            Order::create([
                'store_id' => $this->store->store_id,
                'schedule_id' => $this->schedule->schedule_id,
                'schedule_name' => $this->schedule->schedule_name,
                'p_quantity' => $i * 50,
                'delivery_date' => now()->addDays($i * 3)->format('Y-m-d'),
                'vehicle' => '4t車',
                'comment' => "テスト注文 #{$i}",
            ]);
        }
        
        // View dashboard which should show order history
        $response = $this->get('/store/dashboard');
        $response->assertStatus(200);
        
        // Should see all 3 orders
        $response->assertSee('テスト注文 #1');
        $response->assertSee('テスト注文 #2');
        $response->assertSee('テスト注文 #3');
        $response->assertSee('50');
        $response->assertSee('100');
        $response->assertSee('150');
    }
} 