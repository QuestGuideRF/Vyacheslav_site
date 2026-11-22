<?php
namespace App\Controllers\Admin;
use App\Core\Controller;
abstract class AdminController extends Controller
{
    protected function adminView(string $template, array $data = []): void
    {
        $this->requireAuth();
        $data['adminUser'] = $this->auth()->user();
        $data['unreadMessages'] = $this->app->get('messages')->unreadCount();
        $this->view('admin/' . $template, $data, 'admin');
    }
}