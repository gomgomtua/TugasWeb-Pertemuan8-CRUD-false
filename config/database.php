<?php
/**
 * config/database.php
 * Koneksi PDO dengan Singleton pattern:
 *  - hanya 1 koneksi per request
 *  - konstruktor private (tidak bisa `new Database` dari luar)
 *  - 3 opsi PDO wajib: ERRMODE_EXCEPTION, FETCH_ASSOC, EMULATE_PREPARES=false
 *
 * Pemakaian:
 *   require_once 'config/database.php';
 *   $pdo = Database::getInstance()->getConnection();
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $conn;

    // Private: tidak bisa di-instansiasi dari luar class
    private function __construct()
    {
        // Kredensial bisa diganti lewat environment variable;
        // default-nya cocok untuk XAMPP / Laragon.
        $host = self::env('DB_HOST', 'localhost');
        $name = self::env('DB_NAME', 'inventaris_db');
        $user = self::env('DB_USER', 'root');
        $pass = self::env('DB_PASS', '');

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // error -> exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // hasil: array asosiatif
            PDO::ATTR_EMULATE_PREPARES   => false,                  // real prepared statement
        ];

        try {
            $this->conn = new PDO(
                "mysql:host={$host};dbname={$name};charset=utf8mb4",
                $user,
                $pass,
                $options
            );
        } catch (PDOException $e) {
            // Detail error hanya ke log server, bukan ke layar (cegah information leakage)
            error_log('Koneksi DB gagal: ' . $e->getMessage());
            die('Koneksi database gagal. Pastikan MySQL sudah berjalan dan schema.sql sudah di-import.');
        }
    }

    // Cegah duplikasi instance lewat clone / unserialize
    private function __clone() {}

    public function __wakeup()
    {
        throw new Exception('Singleton tidak boleh di-unserialize.');
    }

    // Satu-satunya cara mengambil objek Database
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }

    private static function env(string $key, string $default): string
    {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }
}
