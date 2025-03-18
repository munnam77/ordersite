<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Middleware\RedirectIfNotRole;
use App\Models\Admin;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $store;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->admin = Admin::create([
            'admin_name' => 'テスト管理者',
            'login_id' => 'admin_test',
            'login_password' => bcrypt('password'),
            'mail' => 'admin@test.com',
        ]);

        $this->store = Store::create([
            'store_id' => 999,
            'prefectures' => 'テスト県',
            'store_name' => 'テスト店舗',
            'login_id' => 'store_test',
            'login_password' => bcrypt('password'),
            'admin' => false,
        ]);
    }

    /**
     * Test RedirectIfNotRole middleware redirects when not authenticated.
     */
    public function test_middleware_redirects_when_not_authenticated(): void
    {
        $middleware = new RedirectIfNotRole();
        $request = Request::create('/admin/dashboard', 'GET');
        
        $response = $middleware->handle($request, function () {
            return 'Passed';
        }, 'admin');
        
        $this->assertInstanceOf('Illuminate\Http\RedirectResponse', $response);
        $this->assertEquals(route('login'), $response->getTargetUrl());
    }

    /**
     * Test admin routes are protected with the admin guard.
     */
    public function test_admin_routes_require_admin_auth(): void
    {
        // Try accessing admin dashboard without auth
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');

        // Log in as a store and try accessing admin dashboard
        $this->actingAs($this->store, 'store');
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');

        // Log in as admin and try accessing admin dashboard
        $this->actingAs($this->admin, 'admin');
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    /**
     * Test store routes are protected with the store guard.
     */
    public function test_store_routes_require_store_auth(): void
    {
        // Try accessing store dashboard without auth
        $response = $this->get('/store/dashboard');
        $response->assertRedirect('/login');

        // Log in as a admin and try accessing store dashboard
        $this->actingAs($this->admin, 'admin');
        $response = $this->get('/store/dashboard');
        $response->assertRedirect('/login');

        // Log in as store and try accessing store dashboard
        $this->actingAs($this->store, 'store');
        $response = $this->get('/store/dashboard');
        $response->assertStatus(200);
    }

    /**
     * Test RedirectIfAuthenticated middleware redirects authenticated users.
     */
    public function test_redirect_if_authenticated_middleware(): void
    {
        // Log in as store user
        $this->actingAs($this->store, 'store');
        
        // Try accessing login page
        $response = $this->get('/login');
        $response->assertRedirect('/store/dashboard');

        // Log out
        Auth::guard('store')->logout();
        
        // Log in as admin user
        $this->actingAs($this->admin, 'admin');
        
        // Try accessing login page
        $response = $this->get('/login');
        $response->assertRedirect('/admin/dashboard');
    }
} 