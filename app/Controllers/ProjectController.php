<?php
namespace App\Controllers;
use App\Core\Controller;
class ProjectController extends Controller
{
    public function show(string $slug, string $lang = 'ru'): void
    {
        $lang = $lang === 'en' ? 'en' : 'ru';
        $this->app->instance('locale', $lang);
        $project = $this->app->get('projects')->findBySlug($slug);
        if (!$project) {
            render_not_found($lang);
            return;
        }
        $settings = $this->settings()->all();
        $meta = [
            'title' => trans_value($project, 'title', $lang) . ' — ' . ($lang === 'ru' ? ($settings['site_title_ru'] ?? '') : ($settings['site_title_en'] ?? '')),
            'description' => str_limit((string) trans_value($project, 'excerpt', $lang), 160),
            'canonical' => $lang === 'ru' ? url('/projects/' . $slug) : url('/en/projects/' . $slug),
            'alternate_ru' => url('/projects/' . $slug),
            'alternate_en' => url('/en/projects/' . $slug),
        ];
        $this->view('public/project', compact('lang', 'project', 'settings', 'meta'));
    }
}