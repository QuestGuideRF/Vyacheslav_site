<?php
namespace App\Controllers\Admin;
use App\Core\Controller;
use App\Core\Response;
class AuthController extends Controller
{
    public function login(): void
    {
        if ($this->auth()->check()) {
            Response::redirect('/admin');
        }
        if ($this->request->method() === 'POST') {
            $this->ensureCsrf();
            $username = trim((string) $this->request->input('username'));
            $password = (string) $this->request->input('password');
            if ($this->auth()->attempt($username, $password)) {
                clear_old();
                flash('success', 'Вход выполнен успешно.');
                Response::redirect('/admin');
            }
            set_old($this->request->input());
            flash('error', 'Неверный логин или пароль.');
            Response::redirect('/admin/login');
        }
        $this->view('admin/login', ['meta' => ['title' => 'Вход в админ-панель']], 'admin');
    }
    public function logout(): void
    {
        $this->auth()->logout();
        flash('success', 'Вы вышли из админ-панели.');
        Response::redirect('/admin/login');
    }
}