<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->view('auth/login', [
            'title' => 'Login - Ocean HRMS',
            'timeout' => isset($_GET['timeout']),
        ], null);
    }

    public function login(): void
    {
        $this->requireCsrf('login', 'Invalid security token. Please try again.');

        $portal = $this->input('portal', 'admin') === 'employee' ? 'employee' : 'admin';
        $login = trim((string) $this->input('login', ''));
        $password = (string) $this->input('password', '');

        if ($login === '' || $password === '') {
            $this->flash('error', 'Please enter credential ID and encryption key.');
            $this->redirect('login');
        }

        if (Auth::isLocked($login)) {
            $this->flash('error', 'Too many failed attempts. Try again after 15 minutes.');
            $this->redirect('login');
        }

        if (!Auth::attempt($login, $password, $portal)) {
            activity_log('auth', 'login_failed', null, null, ['login' => $login, 'portal' => $portal]);
            $this->flash('error', 'Invalid credentials. Access denied.');
            $this->redirect('login');
        }

        activity_log('auth', 'login_success', Auth::id());
        $dest = Auth::isEmployeePortal() ? 'employee/dashboard' : 'dashboard';
        $this->redirect($dest);
    }

    public function logout(): void
    {
        activity_log('auth', 'logout', Auth::id());
        Auth::logout();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->flash('success', 'Session closed securely.');
        $this->redirect('login');
    }

    public function showChangePassword(): void
    {
        $this->view('auth/change-password', [
            'title' => 'Change Password',
            'force' => !empty(Auth::user()['must_change_password']),
        ], 'layouts/app');
    }

    public function changePassword(): void
    {
        $this->requireCsrf('change-password', 'Invalid security token.');

        $current = (string) $this->input('current_password', '');
        $new = (string) $this->input('new_password', '');
        $confirm = (string) $this->input('confirm_password', '');

        if (strlen($new) < 8) {
            $this->flash('error', 'New password must be at least 8 characters.');
            $this->redirect('change-password');
        }

        if ($new !== $confirm) {
            $this->flash('error', 'Password confirmation does not match.');
            $this->redirect('change-password');
        }

        $db = $this->db();
        $stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([Auth::id()]);
        $hash = $stmt->fetchColumn();

        if (!$hash || !password_verify($current, $hash)) {
            $this->flash('error', 'Current password is incorrect.');
            $this->redirect('change-password');
        }

        $stmt = $db->prepare('UPDATE users SET password = ?, login_secret = NULL, must_change_password = 0, updated_at = NOW() WHERE id = ?');
        $stmt->execute([password_hash($new, PASSWORD_DEFAULT), Auth::id()]);
        $_SESSION['user']['must_change_password'] = 0;

        activity_log('auth', 'password_changed', Auth::id());
        $this->flash('success', 'Password updated successfully.');
        $dest = Auth::isEmployeePortal() ? 'employee/dashboard' : 'dashboard';
        $this->redirect($dest);
    }
}
