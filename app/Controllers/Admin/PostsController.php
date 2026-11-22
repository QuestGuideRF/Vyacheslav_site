<?php
namespace App\Controllers\Admin;
use App\Core\Response;
class PostsController extends AdminController
{
    public function index(): void
    {
        $posts = $this->app->get('posts')->all();
        $this->adminView('posts/index', compact('posts'));
    }
    public function create(): void
    {
        $post = null;
        $this->adminView('posts/form', compact('post'));
    }
    public function edit(string $id): void
    {
        $post = $this->app->get('posts')->find((int) $id);
        if (!$post) {
            flash('error', 'Пост не найден.');
            Response::redirect('/admin/posts');
        }
        $this->adminView('posts/form', compact('post'));
    }
    public function store(): void
    {
        $this->savePost();
    }
    public function update(string $id): void
    {
        $this->savePost((int) $id);
    }
    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->ensureCsrf();
        $this->app->get('posts')->delete((int) $id);
        flash('success', 'Пост удалён.');
        Response::redirect('/admin/posts');
    }
    private function savePost(?int $id = null): void
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
            'category_ru' => $this->request->input('category_ru'),
            'category_en' => $this->request->input('category_en'),
            'image_path' => $this->request->input('existing_image'),
            'published' => $this->request->input('published'),
            'published_at' => $this->request->input('published_at'),
        ];
        if ($this->request->input('translate_post')) {
            $translated = $this->app->get('translate')->translateFields([
                'title_en' => (string) $data['title_ru'],
                'excerpt_en' => (string) $data['excerpt_ru'],
                'content_en' => (string) $data['content_ru'],
                'category_en' => (string) $data['category_ru'],
            ]);
            $data = array_merge($data, $translated);
        }
        $upload = $this->app->get('upload')->store($this->request->file('image_file'), 'posts');
        if ($upload) {
            $data['image_path'] = $upload;
        }
        $this->app->get('posts')->save($data, $id);
        flash('success', $id ? 'Пост обновлён.' : 'Пост создан.');
        Response::redirect('/admin/posts');
    }
}