<?php

class Database
{
    private string $host = "localhost";
    private string $dbName = "gamestudio_db";
    private string $username = "root";
    private string $password = "";
    private ?PDO $connection = null;

    public function connect(): PDO
    {
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4",
                $this->username,
                $this->password
            );

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $this->connection;

        } catch (PDOException $exception) {
            http_response_code(500);

            echo json_encode([
                "error" => "Error de conexión con la base de datos",
                "detalle" => $exception->getMessage()
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            exit;
        }
    }
}