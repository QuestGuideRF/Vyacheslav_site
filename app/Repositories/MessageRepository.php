<?php
namespace App\Repositories;
class MessageRepository extends BaseRepository
{
    public function create(array $data): int
    {
        $data = $this->clean($data);
        $this->db->execute(
            'INSERT INTO messages (name, email, telegram, message_text, page_url, locale, ip_address)
             VALUES (:name, :email, :telegram, :message_text, :page_url, :locale, :ip_address)',
            [
                'name' => $data['name'],
                'email' => $data['email'] ?: null,
                'telegram' => $data['telegram'] ?: null,
                'message_text' => $data['message_text'],
                'page_url' => $data['page_url'] ?: null,
                'locale' => $data['locale'] ?: 'ru',
                'ip_address' => $data['ip_address'] ?: null,
            ]
        );
        return (int) $this->db->lastInsertId();
    }
    public function all(): array
    {
        return $this->db->fetchAll('SELECT * FROM messages ORDER BY created_at DESC');
    }
    public function unreadCount(): int
    {
        $row = $this->db->fetch('SELECT COUNT(*) AS total FROM messages WHERE is_read = 0');
        return (int) ($row['total'] ?? 0);
    }
    public function markRead(int $id): void
    {
        $this->db->execute('UPDATE messages SET is_read = 1 WHERE id = :id', ['id' => $id]);
    }
}