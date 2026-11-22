<?php
namespace App\Controllers\Admin;
use App\Core\Response;
class SettingsController extends AdminController
{
    public function edit(): void
    {
        $grouped = $this->settings()->grouped();
        $this->adminView('settings', compact('grouped'));
    }
    public function update(): void
    {
        $this->requireAuth();
        $this->ensureCsrf();
        $general = [
            'site_title_ru' => $this->request->input('site_title_ru'),
            'site_title_en' => $this->request->input('site_title_en'),
            'site_description_ru' => $this->request->input('site_description_ru'),
            'site_description_en' => $this->request->input('site_description_en'),
            'person_full_name' => $this->request->input('person_full_name'),
            'person_full_name_en' => $this->request->input('person_full_name_en'),
            'person_nickname' => $this->request->input('person_nickname'),
            'person_birthdate' => $this->request->input('person_birthdate'),
            'hero_tagline_ru' => $this->request->input('hero_tagline_ru'),
            'hero_tagline_en' => $this->request->input('hero_tagline_en'),
            'hero_intro_ru' => $this->request->input('hero_intro_ru'),
            'hero_intro_en' => $this->request->input('hero_intro_en'),
            'person_biography_ru' => $this->request->input('person_biography_ru'),
            'person_biography_en' => $this->request->input('person_biography_en'),
            'card_roles_ru' => $this->request->input('card_roles_ru'),
            'card_roles_en' => $this->request->input('card_roles_en'),
            'card_status_ru' => $this->request->input('card_status_ru'),
            'card_status_en' => $this->request->input('card_status_en'),
            'card_location_ru' => $this->request->input('card_location_ru'),
            'card_location_en' => $this->request->input('card_location_en'),
            'github_username' => $this->request->input('github_username'),
            'github_token' => $this->request->input('github_token'),
            'telegram_bot_token' => $this->request->input('telegram_bot_token'),
            'telegram_chat_id' => $this->request->input('telegram_chat_id'),
            'contact_email' => $this->request->input('contact_email'),
            'default_theme' => $this->request->input('default_theme', 'dark'),
            'llms_summary_ru' => $this->request->input('llms_summary_ru'),
            'llms_summary_en' => $this->request->input('llms_summary_en'),
        ];
        $seo = [
            'seo_home_title_ru' => $this->request->input('seo_home_title_ru'),
            'seo_home_title_en' => $this->request->input('seo_home_title_en'),
            'seo_home_description_ru' => $this->request->input('seo_home_description_ru'),
            'seo_home_description_en' => $this->request->input('seo_home_description_en'),
            'seo_home_keywords_ru' => $this->request->input('seo_home_keywords_ru'),
            'seo_home_keywords_en' => $this->request->input('seo_home_keywords_en'),
        ];
        if ($this->request->input('translate_general')) {
            $translated = $this->app->get('translate')->translateFields([
                'site_title_en' => (string) $general['site_title_ru'],
                'site_description_en' => (string) $general['site_description_ru'],
                'person_full_name_en' => (string) $general['person_full_name'],
                'hero_tagline_en' => (string) $general['hero_tagline_ru'],
                'hero_intro_en' => (string) $general['hero_intro_ru'],
                'person_biography_en' => (string) $general['person_biography_ru'],
                'card_roles_en' => (string) $general['card_roles_ru'],
                'card_status_en' => (string) $general['card_status_ru'],
                'card_location_en' => (string) $general['card_location_ru'],
                'llms_summary_en' => (string) $general['llms_summary_ru'],
            ]);
            $general = array_merge($general, $translated);
        }
        if ($this->request->input('translate_seo')) {
            $translatedSeo = $this->app->get('translate')->translateFields([
                'seo_home_title_en' => (string) $seo['seo_home_title_ru'],
                'seo_home_description_en' => (string) $seo['seo_home_description_ru'],
                'seo_home_keywords_en' => (string) $seo['seo_home_keywords_ru'],
            ]);
            $seo = array_merge($seo, $translatedSeo);
        }
        $upload = $this->app->get('upload')->store($this->request->file('avatar_file'), 'avatars');
        if ($upload) {
            $general['avatar_path'] = $upload;
        }
        $this->settings()->saveMany('general', $general);
        $this->settings()->saveMany('seo', $seo);
        flash('success', 'Настройки сохранены.');
        Response::redirect('/admin/settings');
    }
}