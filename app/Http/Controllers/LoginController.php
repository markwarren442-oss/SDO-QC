<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin()
    {
        if (session('user_id')) {
            return redirect(session('role') === 'Admin' ? route('admin.dashboard') : route('staff.employeeview'));
        }
        return view('login');
    }

    /**
     * Handle AJAX login
     */
    public function login(Request $request)
    {
        $db = DB::connection();
        $response = ['success' => false, 'message' => ''];

        try {
            $username = trim($request->input('username', ''));
            $password = $request->input('password', '');
            $remember = $request->has('remember_me');

            $user = $db->selectOne("SELECT * FROM users WHERE username = ?", [$username]);

            if ($user && password_verify($password, $user->password)) {
                // Login Success
                session([
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'role' => $user->role,
                    'user_photo' => $user->photo ?? null,
                ]);

                // Log the login
                $ip = $request->ip();
                $db->insert(
                    "INSERT INTO login_logs (username, role, ip_address) VALUES (?, ?, ?)",
                    [$user->username, $user->role, $ip]
                );

                $logId = $db->getPdo()->lastInsertId();
                session(['log_id' => $logId]);

                // Audit log
                try {
                    $db->insert(
                        "INSERT INTO audit_logs (user_id, username, action, module, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)",
                        [$user->id, $user->username, 'Login', 'System', "User logged in (Role: {$user->role})", $ip]
                    );
                } catch (\Exception $e) {
                }

                $response['success'] = true;
                $response['message'] = 'Login Successful';
                $response['redirect'] = ($user->role === 'Admin' ? route('admin.dashboard') : route('staff.employeeview'));
            } else {
                $response['message'] = "Incorrect username or password.";
            }
        } catch (\Exception $e) {
            $response['message'] = "System Error: " . $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $db = DB::connection();

        try {
            if (session('user_id')) {
                // Clear remember token
                $db->update("UPDATE users SET remember_token = NULL WHERE id = ?", [session('user_id')]);

                // Log logout time
                if (session('log_id')) {
                    $db->update("UPDATE login_logs SET logout_time = CURRENT_TIMESTAMP WHERE id = ?", [session('log_id')]);
                }
            }
        } catch (\Exception $e) {
            // Fail silently
        }

        // Destroy session
        $request->session()->flush();

        return redirect()->route('login');
    }
}
