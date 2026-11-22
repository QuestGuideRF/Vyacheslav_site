<?php
namespace App\Controllers\Admin;
use App\Core\Response;
class BlocksController extends AdminController
{
    public function index(): void
    {
        $blocks = $this->app->get('blocks')->all();
        $this->adminView('blocks/index', compact('blocks'));
    }
    public function edit(string $id): void
    {
        $block = $this->app->get('blocks')->find((int) $id);
        if (!$block) {
            flash('error', 'Блок не найден.');
            Response::redirect('/admin/blocks');
        }
        $this->adminView('blocks/form', compact('block'));
    }
    public function update(string $id): void
    {
        $this->requireAuth();
        $this->ensureCsrf();
        $data = [
            'label' => $this->request->input('label'),
            'title_ru' => $this->request->input('title_ru'),
            'title_en' => $this->request->input('title_en'),
            'lead_ru' => $this->request->input('lead_ru'),
            'lead_en' => $this->request->input('lead_en'),
            'content_ru' => $this->request->input('content_ru'),
            'content_en' => $this->request->input('content_en'),
            'extra_json' => $this->request->input('extra_json'),
            'sort_order' => $this->request->input('sort_order', 0),
            'enabled' => $this->request->input('enabled'),
        ];
        if ($this->request->input('translate_block')) {
            $translated = $this->app->get('translate')->translateFields([
                'title_en' => (string) $data['title_ru'],
                'lead_en' => (string) $data['lead_ru'],
                'content_en' => (string) $data['content_ru'],
            ]);
            $data = array_merge($data, $translated);
            $data['extra_json'] = $this->translateExtraJson((string) $data['extra_json']);
        }
        $this->app->get('blocks')->update((int) $id, $data);
        flash('success', 'Блок сохранён.');
        Response::redirect('/admin/blocks');
    }
    private function translateExtraJson(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return $raw;
        }
        $translated = $this->translateExtraNode($decoded);
        return json_encode($translated, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: $raw;
    }
    private function translateExtraNode(mixed $node): mixed
    {
        if (!is_array($node)) {
            return $node;
        }
        foreach ($node as $key => $value) {
            if (is_array($value)) {
                $node[$key] = $this->translateExtraNode($value);
            }
            if (!is_string($value) || !str_ends_with((string) $key, '_ru')) {
                continue;
            }
            $enKey = substr((string) $key, 0, -3) . '_en';
            $node[$enKey] = $this->app->get('translate')->translateText($value);
        }
        return $node;
    }
}