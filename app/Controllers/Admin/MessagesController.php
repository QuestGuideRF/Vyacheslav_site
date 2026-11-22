<?php
namespace App\Controllers\Admin;
use App\Core\Response;
class MessagesController extends AdminController
{
    public function index(): void
    {
        $messages = $this->app->get('messages')->all();
        $this->adminView('messages/index', compact('messages'));
    }
    public function markRead(string $id): void
    {
        $this->requireAuth();
        $this->ensureCsrf();
        $this->app->get('messages')->markRead((int) $id);
        flash('success', 'Сообщение отмечено как прочитанное.');
        Response::redirect('/admin/messages');
    }
}