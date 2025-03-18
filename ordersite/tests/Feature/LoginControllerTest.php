<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'admin_name' => 'テスト管理者',
            'login_id' => 'admin_test',
            'login_password' => bcrypt('password'),
            'mail' => 'admin@test.com',
        ]);

        $this->store = Store::create([
            'store_id' => 1001,
            'prefectures' => '東京都',
            'store_name' => 'テスト東京店',
            'login_id' => 'tokyo_store',
            'login_password' => bcrypt('password'),
            'admin' => false,
        ]);
    }

    /**
     * Test login page loads correctly.
     */
    public function test_login_page_loads()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('ログイン');
        $response->assertSee('ログインID');
        $response->assertSee('パスワード');
    }

    /**
     * Test store login with valid credentials.
     */
    public function test_store_login_with_valid_credentials()
    {
        $response = $this->post('/login', [
            'login_id' => 'tokyo_store',
            'password' => 'password',
            'user_type' => 'store'
        ]);

        $response->assertRedirect('/store/dashboard');
        $this->assertAuthenticatedAs($this->store, 'store');
    }

    /**
     * Test admin login with valid credentials.
     */
    public function test_admin_login_with_valid_credentials()
    {
        $response = $this->post('/login', [
            'login_id' => 'admin_test',
            'password' => 'password',
            'user_type' => 'admin'
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($this->admin, 'admin');
    }

    /**
     * Test store login with invalid credentials.
     */
    public function test_store_login_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'login_id' => 'tokyo_store',
            'password' => 'wrong_password',
            'user_type' => 'store'
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('login_failed');
        $this->assertGuest('store');
    }

    /**
     * Test admin login with invalid credentials.
     */
    public function test_admin_login_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'login_id' => 'admin_test',
            'password' => 'wrong_password',
            'user_type' => 'admin'
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('login_failed');
        $this->assertGuest('admin');
    }

    /**
     * Test login with missing user type.
     */
    public function test_login_with_missing_user_type()
    {
        $response = $this->post('/login', [
            'login_id' => 'admin_test',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('user_type');
    }

    /**
     * Test login validation for required fields.
     */
    public function test_login_validation_for_required_fields()
    {
        $response = $this->post('/login', []);
        
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['login_id', 'password', 'user_type']);
    }

    /**
     * Test logout functionality for store.
     */
    public function test_store_logout()
    {
        $this->actingAs($this->store, 'store');
        $this->assertAuthenticatedAs($this->store, 'store');
        
        $response = $this->post('/logout');
        
        $response->assertRedirect('/');
        $this->assertGuest('store');
    }

    /**
     * Test logout functionality for admin.
     */
    public function test_admin_logout()
    {
        $this->actingAs($this->admin, 'admin');
        $this->assertAuthenticatedAs($this->admin, 'admin');
        
        $response = $this->post('/logout');
        
        $response->assertRedirect('/');
        $this->assertGuest('admin');
    }

    /**
     * Test redirect if already authenticated as store.
     */
    public function test_redirect_if_authenticated_as_store()
    {
        $this->actingAs($this->store, 'store');
        
        $response = $this->get('/login');
        
        $response->assertRedirect('/store/dashboard');
    }

    /**
     * Test redirect if already authenticated as admin.
     */
    public function test_redirect_if_authenticated_as_admin()
    {
        $this->actingAs($this->admin, 'admin');
        
        $response = $this->get('/login');
        
        $response->assertRedirect('/admin/dashboard');
    }
} 