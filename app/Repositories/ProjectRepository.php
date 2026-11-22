<?php
namespace App\Repositories;
class ProjectRepository extends BaseRepository
{
    public function all(bool $publishedOnly = false): array
    {
        $sql = 'SELECT * FROM projects';
        if ($publishedOnly) {
            $sql .= ' WHERE published = 1';
        }
        $sql .= ' ORDER BY sort_order, updated_at DESC';
        return $this->db->fetchAll($sql);
    }
    public function featured(int $limit = 6): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM projects WHERE published = 1 ORDER BY featured DESC, sort_order, updated_at DESC LIMIT ' . (int) $limit
        );
    }
    public function latest(int $limit = 4): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM projects WHERE published = 1 ORDER BY updated_at DESC LIMIT ' . (int) $limit
        );
    }
    public function find(int $id): ?array
    {
        return $this->db->fetch('SELECT * FROM projects WHERE id = :id', ['id' => $id]);
    }
    public function findBySlug(string $slug): ?array
    {
        return $this->db->fetch('SELECT * FROM projects WHERE slug = :slug AND published = 1', ['slug' => $slug]);
    }
    public function save(array $data, ?int $id = null): int
    {
        $data = $this->clean($data);
        $payload = [
            'slug' => $data['slug'],
            'title_ru' => $data['title_ru'],
            'title_en' => $data['title_en'] ?: null,
            'excerpt_ru' => $data['excerpt_ru'] ?: null,
            'excerpt_en' => $data['excerpt_en'] ?: null,
            'content_ru' => $data['content_ru'] ?: null,
            'content_en' => $data['content_en'] ?: null,
            'status_ru' => $data['status_ru'] ?: null,
            'status_en' => $data['status_en'] ?: null,
            'stack_text' => $data['stack_text'] ?: null,
            'live_url' => $data['live_url'] ?: null,
            'repo_url' => $data['repo_url'] ?: null,
            'demo_url' => $data['demo_url'] ?: null,
            'image_path' => $data['image_path'] ?: null,
            'featured' => empty($data['featured']) ? 0 : 1,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'published' => empty($data['published']) ? 0 : 1,
        ];
        if ($id) {
            $payload['id'] = $id;
            $this->db->execute(
                'UPDATE projects SET
                    slug = :slug, title_ru = :title_ru, title_en = :title_en, excerpt_ru = :excerpt_ru, excerpt_en = :excerpt_en,
                    content_ru = :content_ru, content_en = :content_en, status_ru = :status_ru, status_en = :status_en,
                    stack_text = :stack_text, live_url = :live_url, repo_url = :repo_url, demo_url = :demo_url,
                    image_path = :image_path, featured = :featured, sort_order = :sort_order, published = :published
                 WHERE id = :id',
                $payload
            );
            return $id;
        }
        $this->db->execute(
            'INSERT INTO projects
             (slug, title_ru, title_en, excerpt_ru, excerpt_en, content_ru, content_en, status_ru, status_en, stack_text, live_url, repo_url, demo_url, image_path, featured, sort_order, published)
             VALUES
             (:slug, :title_ru, :title_en, :excerpt_ru, :excerpt_en, :content_ru, :content_en, :status_ru, :status_en, :stack_text, :live_url, :repo_url, :demo_url, :image_path, :featured, :sort_order, :published)',
            $payload
        );
        return (int) $this->db->lastInsertId();
    }
    public function delete(int $id): void
    {
        $this->db->execute('DELETE FROM projects WHERE id = :id', ['id' => $id]);
    }
}
