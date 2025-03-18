<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login_id' => ['required'],
            'login_password' => ['required'],
        ], [
            'login_id.required' => 'ログインIDを入力してください',
            'login_password.required' => 'パスワードを入力してください',
        ]);

        // Try admin authentication
        if (Auth::guard('admin')->attempt([
            'login_id' => $credentials['login_id'],
            'login_password' => $credentials['login_password']
        ])) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        // Try store authentication
        if (Auth::guard('store')->attempt([
            'login_id' => $credentials['login_id'],
            'login_password' => $credentials['login_password']
        ])) {
            $request->session()->regenerate();
            return redirect()->intended(route('store.dashboard'));
        }

        return back()->withErrors([
            'login_id' => 'ログインIDまたはパスワードが正しくありません',
        ])->onlyInput('login_id');
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        } else if (Auth::guard('store')->check()) {
            Auth::guard('store')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
} 