<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\Store;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login page loads correctly.
     */
    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('発注サイト');
        $response->assertSee('ログインID');
        $response->assertSee('パスワード');
    }

    /**
     * Test admin can login.
     */
    public function test_admin_can_login(): void
    {
        // Create admin
        $admin = Admin::create([
            'admin_name' => 'テスト管理者',
            'login_id' => 'admin_test',
            'login_password' => Hash::make('password'),
            'mail' => 'admin@test.com',
        ]);

        $response = $this->post('/login', [
            'login_id' => 'admin_test',
            'login_password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    /**
     * Test store can login.
     */
    public function test_store_can_login(): void
    {
        // Create store
        $store = Store::create([
            'store_id' => 999,
            'prefectures' => 'テスト県',
            'store_name' => 'テスト店舗',
            'login_id' => 'store_test',
            'login_password' => Hash::make('password'),
            'admin' => false,
        ]);

        $response = $this->post('/login', [
            'login_id' => 'store_test',
            'login_password' => 'password',
        ]);

        $response->assertRedirect('/store/dashboard');
        $this->assertAuthenticatedAs($store, 'store');
    }

    /**
     * Test login with invalid credentials.
     */
    public function test_login_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'login_id' => 'wrong_id',
            'login_password' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors('login_id');
        $this->assertGuest('admin');
        $this->assertGuest('store');
    }

    /**
     * Test logout functionality.
     */
    public function test_logout(): void
    {
        // Create and login as store
        $store = Store::create([
            'store_id' => 999,
            'prefectures' => 'テスト県',
            'store_name' => 'テスト店舗',
            'login_id' => 'store_test',
            'login_password' => Hash::make('password'),
            'admin' => false,
        ]);

        $this->actingAs($store, 'store');
        $this->assertAuthenticatedAs($store, 'store');

        $response = $this->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest('store');
    }

    /**
     * Test middleware redirects unauthenticated users.
     */
    public function test_middleware_redirects_unauthenticated_users(): void
    {
        $response = $this->get('/store/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }
} 