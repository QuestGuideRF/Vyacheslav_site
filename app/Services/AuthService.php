<?php
namespace App\Services;
use App\Core\Database;
class AuthService
{
    private Database $db;
    public function __construct(Database $db)
    {
        $this->db = $db;
    }
    public function attempt(string $username, string $password): bool
    {
        $admin = $this->db->fetch('SELECT * FROM admins WHERE username = :username', ['username' => $username]);
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            return false;
        }
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['display_name'];
        $_SESSION['admin_username'] = $admin['username'];
        $this->db->execute('UPDATE admins SET last_login_at = NOW() WHERE id = :id', ['id' => $admin['id']]);
        return true;
    }
    public function check(): bool
    {
        return !empty($_SESSION['admin_id']);
    }
    public function user(): ?array
    {
        if (!$this->check()) {
            return null;
        }
        return [
            'id' => $_SESSION['admin_id'],
            'display_name' => $_SESSION['admin_name'],
            'username' => $_SESSION['admin_username'],
        ];
    }
    public function logout(): void
    {
        unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_username']);
    }
}