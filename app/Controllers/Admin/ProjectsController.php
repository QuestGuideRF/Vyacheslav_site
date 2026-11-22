<?php
namespace App\Controllers\Admin;
use App\Core\Response;
class ProjectsController extends AdminController
{
    public function index(): void
    {
        $projects = $this->app->get('projects')->all();
        $this->adminView('projects/index', compact('projects'));
    }
    public function create(): void
    {
        $project = null;
        $this->adminView('projects/form', compact('project'));
    }
    public function edit(string $id): void
    {
        $project = $this->app->get('projects')->find((int) $id);
        if (!$project) {
            flash('error', 'Проект не найден.');
            Response::redirect('/admin/projects');
        }
        $this->adminView('projects/form', compact('project'));
    }
    public function store(): void
    {
        $this->saveProject();
    }
    public function update(string $id): void
    {
        $this->saveProject((int) $id);
    }
    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->ensureCsrf();
        $this->app->get('projects')->delete((int) $id);
        flash('success', 'Проект удалён.');
        Response::redirect('/admin/projects');
    }
    private function saveProject(?int $id = null): void
    {
        $this->requireAuth();
        $this->ensureCsrf();
        $data = [
            'slug' => slugify((string) $this->request->input('slug') ?: (string) $this->request->input('title_ru')),
            'title_ru' => $this->request->input('title_ru'),
            'title_en' => $this->request->input('title_en'),
            'excerpt_ru' => $this->request->input('excerpt_ru'),
            'excerpt_en' => $this->request->input('excerpt_en'),
            'content_ru' => $this->request->input('content_ru'),
            'content_en' => $this->request->input('content_en'),
            'status_ru' => $this->request->input('status_ru'),
            'status_en' => $this->request->input('status_en'),
            'stack_text' => $this->request->input('stack_text'),
            'live_url' => $this->request->input('live_url'),
            'repo_url' => $this->request->input('repo_url'),
            'demo_url' => $this->request->input('demo_url'),
            'image_path' => $this->request->input('existing_image'),
            'featured' => $this->request->input('featured'),
            'sort_order' => $this->request->input('sort_order', 0),
            'published' => $this->request->input('published'),
        ];
        if ($this->request->input('translate_project')) {
            $translated = $this->app->get('translate')->translateFields([
                'title_en' => (string) $data['title_ru'],
                'excerpt_en' => (string) $data['excerpt_ru'],
                'content_en' => (string) $data['content_ru'],
                'status_en' => (string) $data['status_ru'],
            ]);
            $data = array_merge($data, $translated);
        }
        $upload = $this->app->get('upload')->store($this->request->file('image_file'), 'projects');
        if ($upload) {
            $data['image_path'] = $upload;
        }
        $this->app->get('projects')->save($data, $id);
        flash('success', $id ? 'Проект обновлён.' : 'Проект создан.');
        Response::redirect('/admin/projects');
    }
}