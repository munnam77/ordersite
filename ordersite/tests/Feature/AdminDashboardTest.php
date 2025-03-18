<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\Store;
use App\Models\Schedule;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $store;
    protected $schedule;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a test admin
        $this->admin = Admin::create([
            'admin_name' => 'テスト管理者',
            'login_id' => 'admin_test',
            'login_password' => Hash::make('password'),
            'mail' => 'admin@test.com',
        ]);

        // Create a test store
        $this->store = Store::create([
            'store_id' => 999,
            'prefectures' => 'テスト県',
            'store_name' => 'テスト店舗',
            'login_id' => 'store_test',
            'login_password' => Hash::make('password'),
            'admin' => false,
        ]);

        // Create a test schedule
        $this->schedule = Schedule::create([
            'schedule_id' => 999,
            'schedule_name' => 'テストスケジュール',
            'p_total_number' => 100,
        ]);

        // Create a test order
        Order::create([
            'store_id' => $this->store->id,
            'schedule_id' => $this->schedule->id,
            'schedule_name' => $this->schedule->schedule_name,
            'p_quantity' => 30,
            'delivery_date' => now()->addDays(7),
            'vehicle' => 'テスト車両',
            'comment' => 'テストコメント',
        ]);
    }

    /**
     * Test admin dashboard page loads correctly.
     */
    public function test_admin_dashboard_loads(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertSee('スケジュール管理');
        $response->assertSee($this->schedule->schedule_name);
        $response->assertSee('新規スケジュール');
    }

    /**
     * Test admin can create a new schedule.
     */
    public function test_admin_can_create_schedule(): void
    {
        $scheduleData = [
            'schedule_name' => '新規テストスケジュール',
            'p_total_number' => 200,
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post('/admin/schedule', $scheduleData);
        
        $response->assertRedirect('/admin/dashboard');
        $response->assertSessionHas('success');

        // Check that the schedule was created in the database
        $this->assertDatabaseHas('schedules', [
            'schedule_name' => '新規テストスケジュール',
            'p_total_number' => 200,
        ]);
    }

    /**
     * Test admin can view schedule details.
     */
    public function test_admin_can_view_schedule_details(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/schedule/' . $this->schedule->id);
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.schedule');
        $response->assertSee($this->schedule->schedule_name);
        $response->assertSee('総数量上限');
        $response->assertSee('100'); // Schedule total limit
        $response->assertSee('発注一覧');
        $response->assertSee($this->store->store_name);
        $response->assertSee('30'); // Order quantity
    }

    /**
     * Test admin can update a schedule.
     */
    public function test_admin_can_update_schedule(): void
    {
        $updateData = [
            'schedule_name' => '更新テストスケジュール',
            'p_total_number' => 150,
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->put('/admin/schedule/' . $this->schedule->id, $updateData);
        
        $response->assertRedirect('/admin/schedule/' . $this->schedule->id);
        $response->assertSessionHas('success');

        // Check that the schedule was updated in the database
        $this->assertDatabaseHas('schedules', [
            'id' => $this->schedule->id,
            'schedule_name' => '更新テストスケジュール',
            'p_total_number' => 150,
        ]);
    }

    /**
     * Test admin cannot reduce schedule total below ordered quantity.
     */
    public function test_admin_cannot_reduce_schedule_below_ordered_quantity(): void
    {
        $updateData = [
            'schedule_name' => $this->schedule->schedule_name,
            'p_total_number' => 20, // Currently there's an order with quantity 30
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->put('/admin/schedule/' . $this->schedule->id, $updateData);
        
        $response->assertSessionHasErrors('p_total_number');

        // Check that the schedule was not updated in the database
        $this->assertDatabaseHas('schedules', [
            'id' => $this->schedule->id,
            'p_total_number' => 100, // Original value should remain
        ]);
    }

    /**
     * Test admin schedule validation.
     */
    public function test_schedule_validation(): void
    {
        // Test with missing schedule name
        $response = $this->actingAs($this->admin, 'admin')
            ->post('/admin/schedule', [
                'p_total_number' => 200,
            ]);
        
        $response->assertSessionHasErrors('schedule_name');

        // Test with invalid total number
        $response = $this->actingAs($this->admin, 'admin')
            ->post('/admin/schedule', [
                'schedule_name' => 'テストスケジュール',
                'p_total_number' => 0,
            ]);
        
        $response->assertSessionHasErrors('p_total_number');

        // Test with duplicate schedule name
        $response = $this->actingAs($this->admin, 'admin')
            ->post('/admin/schedule', [
                'schedule_name' => $this->schedule->schedule_name, // Already exists
                'p_total_number' => 200,
            ]);
        
        $response->assertSessionHasErrors('schedule_name');
    }

    /**
     * Test admin can export schedule as CSV.
     */
    public function test_admin_can_export_schedule(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/schedule/' . $this->schedule->id . '/export');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition', 'attachment; filename="schedule_' . $this->schedule->schedule_id . '_' . $this->schedule->schedule_name . '.csv"');
        
        // Check CSV content
        $content = $response->getContent();
        $this->assertStringContainsString($this->store->store_name, $content);
        $this->assertStringContainsString('30', $content); // Order quantity
        $this->assertStringContainsString('テストコメント', $content);
    }
} 