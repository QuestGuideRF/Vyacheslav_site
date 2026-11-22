<?php
namespace App\Repositories;
class PostRepository extends BaseRepository
{
    public function all(bool $publishedOnly = false): array
    {
        $sql = 'SELECT * FROM posts';
        if ($publishedOnly) {
            $sql .= ' WHERE published = 1';
        }
        $sql .= ' ORDER BY published_at DESC, id DESC';
        return $this->db->fetchAll($sql);
    }
    public function latest(int $limit = 4): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM posts WHERE published = 1 ORDER BY published_at DESC LIMIT ' . (int) $limit
        );
    }
    public function find(int $id): ?array
    {
        return $this->db->fetch('SELECT * FROM posts WHERE id = :id', ['id' => $id]);
    }
    public function findBySlug(string $slug): ?array
    {
        return $this->db->fetch('SELECT * FROM posts WHERE slug = :slug AND published = 1', ['slug' => $slug]);
    }
    public function save(array $data, ?int $id = null): int
    {
        $data = $this->clean($data);
        $publishedAt = $data['published_at'] ?: date('Y-m-d H:i:s');
        $payload = [
            'slug' => $data['slug'],
            'title_ru' => $data['title_ru'],
            'title_en' => $data['title_en'] ?: null,
            'excerpt_ru' => $data['excerpt_ru'] ?: null,
            'excerpt_en' => $data['excerpt_en'] ?: null,
            'content_ru' => $data['content_ru'] ?: null,
            'content_en' => $data['content_en'] ?: null,
            'category_ru' => $data['category_ru'] ?: null,
            'category_en' => $data['category_en'] ?: null,
            'image_path' => $data['image_path'] ?: null,
            'published' => empty($data['published']) ? 0 : 1,
            'published_at' => date('Y-m-d H:i:s', strtotime($publishedAt)),
        ];
        if ($id) {
            $payload['id'] = $id;
            $this->db->execute(
                'UPDATE posts SET
                    slug = :slug, title_ru = :title_ru, title_en = :title_en, excerpt_ru = :excerpt_ru, excerpt_en = :excerpt_en,
                    content_ru = :content_ru, content_en = :content_en, category_ru = :category_ru, category_en = :category_en,
                    image_path = :image_path, published = :published, published_at = :published_at
                 WHERE id = :id',
                $payload
            );
            return $id;
        }
        $this->db->execute(
            'INSERT INTO posts
             (slug, title_ru, title_en, excerpt_ru, excerpt_en, content_ru, content_en, category_ru, category_en, image_path, published, published_at)
             VALUES
             (:slug, :title_ru, :title_en, :excerpt_ru, :excerpt_en, :content_ru, :content_en, :category_ru, :category_en, :image_path, :published, :published_at)',
            $payload
        );
        return (int) $this->db->lastInsertId();
    }
    public function delete(int $id): void
    {
        $this->db->execute('DELETE FROM posts WHERE id = :id', ['id' => $id]);
    }
}
