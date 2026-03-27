<?php

class DBCore
{
    private static $instance = null;
    private $pdo;

    private $host = "localhost";
    private $db   = "bymquiz";
    private $user = "root";
    private $pass = "";
    private $charset = "utf8mb4";

    // Private constructor (Singleton)
    private function __construct()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => false
            ]);
        } catch (PDOException $e) {
            die("Database Connection Error: " . $e->getMessage());
        }
    }

    // Get Singleton instance
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new DBCore();
        }
        return self::$instance;
    }

    // Get PDO connection
    public function getConnection()
    {
        return $this->pdo;
    }

    // Optional helper: run query
    public function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

        // ========================
    // SELECT ONE
    // ========================
    public function selectOne($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    // ========================
    // SELECT ALL
    // ========================
    public function selectAll($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ========================
    // INSERT
    // ========================
    public function insert($table, $data)
    {
        $columns = array_keys($data);
        $fields = implode(",", $columns);
        $placeholders = ":" . implode(", :", $columns);

        $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();

        return $this->pdo->lastInsertId();
    }

    // ========================
    // UPDATE
    // ========================
    public function update($table, $data, $where, $whereParams = [])
    {
        $set = [];

        foreach ($data as $key => $value) {
            $set[] = "$key = :$key";
        }

        $setString = implode(", ", $set);

        $sql = "UPDATE $table SET $setString WHERE $where";
        $stmt = $this->pdo->prepare($sql);

        // bind data
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        // bind where params
        foreach ($whereParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        return $stmt->execute();
    }

    // ========================
    // DELETE
    // ========================
    public function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}