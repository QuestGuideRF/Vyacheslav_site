<?php
namespace App\Repositories;
class ContactRepository extends BaseRepository
{
    public function all(bool $enabledOnly = false): array
    {
        $sql = 'SELECT * FROM contacts';
        if ($enabledOnly) {
            $sql .= ' WHERE enabled = 1';
        }
        $sql .= ' ORDER BY sort_order, id';
        return $this->db->fetchAll($sql);
    }
    public function find(int $id): ?array
    {
        return $this->db->fetch('SELECT * FROM contacts WHERE id = :id', ['id' => $id]);
    }
    public function save(array $data, ?int $id = null): int
    {
        $data = $this->clean($data);
        $payload = [
            'label_ru' => $data['label_ru'],
            'label_en' => $data['label_en'] ?: null,
            'value_text' => $data['value_text'],
            'link_url' => $data['link_url'] ?: null,
            'type' => $data['type'] ?: 'link',
            'icon_name' => $data['icon_name'] ?: null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'enabled' => empty($data['enabled']) ? 0 : 1,
        ];
        if ($id) {
            $payload['id'] = $id;
            $this->db->execute(
                'UPDATE contacts SET label_ru = :label_ru, label_en = :label_en, value_text = :value_text,
                 link_url = :link_url, type = :type, icon_name = :icon_name, sort_order = :sort_order, enabled = :enabled
                 WHERE id = :id',
                $payload
            );
            return $id;
        }
        $this->db->execute(
            'INSERT INTO contacts (label_ru, label_en, value_text, link_url, type, icon_name, sort_order, enabled)
             VALUES (:label_ru, :label_en, :value_text, :link_url, :type, :icon_name, :sort_order, :enabled)',
            $payload
        );
        return (int) $this->db->lastInsertId();
    }
    public function delete(int $id): void
    {
        $this->db->execute('DELETE FROM contacts WHERE id = :id', ['id' => $id]);
    }
}
