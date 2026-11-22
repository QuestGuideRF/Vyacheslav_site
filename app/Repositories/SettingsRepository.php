<?php
namespace App\Repositories;
class SettingsRepository extends BaseRepository
{
    private ?array $cache = null;
    public function all(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }
        $rows = $this->db->fetchAll('SELECT `group`, `key`, value_long FROM settings ORDER BY `group`, `key`');
        $data = [];
        foreach ($rows as $row) {
            $data[$row['key']] = $row['value_long'];
        }
        $this->cache = $data;
        return $data;
    }
    public function grouped(): array
    {
        $rows = $this->db->fetchAll('SELECT `group`, `key`, value_long FROM settings ORDER BY `group`, `key`');
        $data = [];
        foreach ($rows as $row) {
            $data[$row['group']][$row['key']] = $row['value_long'];
        }
        return $data;
    }
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }
    public function saveMany(string $group, array $values): void
    {
        foreach ($values as $key => $value) {
            $exists = $this->db->fetch('SELECT id FROM settings WHERE `key` = :key', ['key' => $key]);
            if ($exists) {
                $this->db->execute(
                    'UPDATE settings SET `group` = :group_name, value_long = :value WHERE `key` = :key',
                    ['group_name' => $group, 'value' => $value, 'key' => $key]
                );
            } else {
                $this->db->execute(
                    'INSERT INTO settings (`group`, `key`, value_long) VALUES (:group_name, :key, :value)',
                    ['group_name' => $group, 'key' => $key, 'value' => $value]
                );
            }
        }
        $this->cache = null;
    }
}