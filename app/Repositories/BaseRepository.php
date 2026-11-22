<?php
namespace App\Repositories;
use App\Core\Database;
abstract class BaseRepository
{
    protected Database $db;
    public function __construct(Database $db)
    {
        $this->db = $db;
    }
    protected function clean(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            }
        }
        return $data;
    }
}