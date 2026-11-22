<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Response;
class HomeController extends Controller
{
    public function index(string $lang = 'ru'): void
    {
        $lang = $lang === 'en' ? 'en' : 'ru';
        $this->app->instance('locale', $lang);
        $settings = $this->settings()->all();
        $blocks = $this->app->get('blocks')->keyed(true);
        $projects = $this->app->get('projects')->featured(6);
        $posts = $this->app->get('posts')->latest(4);
        $contacts = $this->app->get('contacts')->all(true);
        $github = $this->app->get('github')->getDashboardData();
        $meta = [
            'title' => $lang === 'ru' ? ($settings['seo_home_title_ru'] ?? $settings['site_title_ru']) : ($settings['seo_home_title_en'] ?? $settings['site_title_en']),
            'description' => $lang === 'ru' ? ($settings['seo_home_description_ru'] ?? $settings['site_description_ru']) : ($settings['seo_home_description_en'] ?? $settings['site_description_en']),
            'keywords' => $lang === 'ru' ? ($settings['seo_home_keywords_ru'] ?? '') : ($settings['seo_home_keywords_en'] ?? ''),
            'canonical' => $lang === 'ru' ? url('/') : url('/en'),
            'alternate_ru' => url('/'),
            'alternate_en' => url('/en'),
            'image' => !empty($settings['avatar_path'])
                ? upload_url($settings['avatar_path'])
                : ($github['profile']['avatar_url'] ?? null),
        ];
        $this->view('public/home', compact('lang', 'settings', 'blocks', 'projects', 'posts', 'contacts', 'github', 'meta'));
    }
    public function contact(): void
    {
        $this->ensureCsrf();
        $data = [
            'name' => trim((string) $this->request->input('name')),
            'email' => trim((string) $this->request->input('email')),
            'telegram' => trim((string) $this->request->input('telegram')),
            'message_text' => trim((string) $this->request->input('message')),
            'page_url' => trim((string) $this->request->input('page_url')),
            'locale' => trim((string) $this->request->input('locale', 'ru')),
            'ip_address' => (string) $this->request->server('REMOTE_ADDR', ''),
        ];
        if ($data['name'] === '' || $data['message_text'] === '') {
            flash('error', $data['locale'] === 'en' ? 'Пожалуйста, введите свое имя и сообщение.' : 'Заполни имя и сообщение.');
            set_old($this->request->input());
            $this->back('/');
        }
        $this->app->get('messages')->create($data);
        $this->app->get('telegram')->sendContactMessage($data);
        clear_old();
        flash('success', $data['locale'] === 'en' ? 'Сообщение успешно отправлено.' : 'Сообщение успешно отправлено.');
        $this->back('/');
    }
}