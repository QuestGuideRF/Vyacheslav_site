<?php
namespace App\Controllers\Admin;
use App\Core\Response;
class DashboardController extends AdminController
{
    public function index(): void
    {
        $github = $this->app->get('github')->getDashboardData();
        $stats = [
            'projects' => count($this->app->get('projects')->all()),
            'posts' => count($this->app->get('posts')->all()),
            'contacts' => count($this->app->get('contacts')->all()),
            'messages' => count($this->app->get('messages')->all()),
        ];
        $this->adminView('dashboard', compact('github', 'stats'));
    }
    public function refreshGithub(): void
    {
        $this->requireAuth();
        $this->ensureCsrf();
        $github = $this->app->get('github')->getDashboardData(true);
        if (!empty($github['meta']['warning'])) {
            flash('error', (string) $github['meta']['warning']);
        } else {
            flash('success', 'GitHub-данные обновлены.');
        }
        Response::redirect('/admin');
    }
}