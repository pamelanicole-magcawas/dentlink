<?php
class Database {

    private $host = "localhost";     
    private $db_name = "dental_clinic"; 
    private $username = "root";       
    private $password = "";           
    private $conn;

    public function getConnect() {
        if ($this->conn == null) {
            try {
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                    $this->username,
                    $this->password
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Database connection error: " . $e->getMessage());
            }
        }
        return $this->conn;
    }
}
?>
