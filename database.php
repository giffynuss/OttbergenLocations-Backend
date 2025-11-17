<?php

class Database {
    private $host = "localhost";
    private $db_name = "ottbergen_booking";   // <-- Ändern!
    private $username = "root";             // XAMPP Default
    private $password = "";                 // XAMPP hat kein Passwort
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );

            // UTF8 für Umlaute
            $this->conn->exec("SET NAMES utf8mb4");

        } catch(PDOException $exception) {
            echo "Database connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
