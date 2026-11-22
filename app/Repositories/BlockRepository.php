<?php
namespace App\Repositories;
class BlockRepository extends BaseRepository
{
    public function all(bool $enabledOnly = false): array
    {
        $sql = 'SELECT * FROM content_blocks';
        if ($enabledOnly) {
            $sql .= ' WHERE enabled = 1';
        }
        $sql .= ' ORDER BY sort_order, id';
        return $this->db->fetchAll($sql);
    }
    public function keyed(bool $enabledOnly = true): array
    {
        $data = [];
        foreach ($this->all($enabledOnly) as $block) {
            $data[$block['section_key']] = $block;
        }
        return $data;
    }
    public function find(int $id): ?array
    {
        return $this->db->fetch('SELECT * FROM content_blocks WHERE id = :id', ['id' => $id]);
    }
    public function update(int $id, array $data): void
    {
        $data = $this->clean($data);
        $this->db->execute(
            'UPDATE content_blocks
             SET label = :label, title_ru = :title_ru, title_en = :title_en, lead_ru = :lead_ru, lead_en = :lead_en,
                 content_ru = :content_ru, content_en = :content_en, extra_json = :extra_json, sort_order = :sort_order, enabled = :enabled
             WHERE id = :id',
            [
                'id' => $id,
                'label' => $data['label'],
                'title_ru' => $data['title_ru'],
                'title_en' => $data['title_en'],
                'lead_ru' => $data['lead_ru'],
                'lead_en' => $data['lead_en'],
                'content_ru' => $data['content_ru'],
                'content_en' => $data['content_en'],
                'extra_json' => $data['extra_json'] ?: null,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'enabled' => empty($data['enabled']) ? 0 : 1,
            ]
        );
    }
}
