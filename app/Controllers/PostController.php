<?php
namespace App\Controllers;
use App\Core\Controller;
class PostController extends Controller
{
    public function show(string $slug, string $lang = 'ru'): void
    {
        $lang = $lang === 'en' ? 'en' : 'ru';
        $this->app->instance('locale', $lang);
        $post = $this->app->get('posts')->findBySlug($slug);
        if (!$post) {
            render_not_found($lang);
            return;
        }
        $settings = $this->settings()->all();
        $meta = [
            'title' => trans_value($post, 'title', $lang) . ' — ' . ($lang === 'ru' ? ($settings['site_title_ru'] ?? '') : ($settings['site_title_en'] ?? '')),
            'description' => str_limit((string) trans_value($post, 'excerpt', $lang), 160),
            'canonical' => $lang === 'ru' ? url('/posts/' . $slug) : url('/en/posts/' . $slug),
            'alternate_ru' => url('/posts/' . $slug),
            'alternate_en' => url('/en/posts/' . $slug),
        ];
        $this->view('public/post', compact('lang', 'post', 'settings', 'meta'));
    }
}