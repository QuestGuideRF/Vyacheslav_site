<?php
namespace App\Controllers\Admin;
use App\Core\Response;
class ContactsController extends AdminController
{
    public function index(): void
    {
        $contacts = $this->app->get('contacts')->all();
        $this->adminView('contacts/index', compact('contacts'));
    }
    public function create(): void
    {
        $contact = null;
        $this->adminView('contacts/form', compact('contact'));
    }
    public function edit(string $id): void
    {
        $contact = $this->app->get('contacts')->find((int) $id);
        if (!$contact) {
            flash('error', 'Контакт не найден.');
            Response::redirect('/admin/contacts');
        }
        $this->adminView('contacts/form', compact('contact'));
    }
    public function store(): void
    {
        $this->saveContact();
    }
    public function update(string $id): void
    {
        $this->saveContact((int) $id);
    }
    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->ensureCsrf();
        $this->app->get('contacts')->delete((int) $id);
        flash('success', 'Контакт удалён.');
        Response::redirect('/admin/contacts');
    }
    private function saveContact(?int $id = null): void
    {
        $this->requireAuth();
        $this->ensureCsrf();
        $data = [
            'label_ru' => $this->request->input('label_ru'),
            'label_en' => $this->request->input('label_en'),
            'value_text' => $this->request->input('value_text'),
            'link_url' => $this->request->input('link_url'),
            'type' => $this->request->input('type'),
            'icon_name' => $this->request->input('icon_name'),
            'sort_order' => $this->request->input('sort_order', 0),
            'enabled' => $this->request->input('enabled'),
        ];
        if ($this->request->input('translate_contact')) {
            $data['label_en'] = $this->app->get('translate')->translateText((string) $data['label_ru']);
        }
        $this->app->get('contacts')->save($data, $id);
        flash('success', $id ? 'Контакт обновлён.' : 'Контакт создан.');
        Response::redirect('/admin/contacts');
    }
}