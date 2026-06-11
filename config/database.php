<?php

class Database
{
    private $host = 'localhost';
    private $dbName = 'db_logistik_cargo';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';

    /**
     * Kembalikan instance PDO untuk koneksi database.
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset={$this->charset}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO($dsn, $this->username, $this->password, $options);
    }
}
