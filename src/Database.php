<?php

class Database {
    private static ?PDO $pdo = null;
    private string $host = 'mysql';
    private string $username = 'app_user';
    private string $password = 'app_password';
    private string $database = 'app_db';
    private mixed $charset = 'utf8mb4';

    public function __construct() {
        $this->connect();
    }

    private function connect(): void {
        if (self::$pdo !== null) {
            return;
        }

        $dsn = "mysql:host=$this->host;dbname=$this->database;charset=$this->charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            self::$pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            new self(); // Создаем экземпляр, который инициализирует соединение
        }

        if (self::$pdo === null) {
            throw new RuntimeException("Database connection is not established");
        }
        return self::$pdo;
    }

}