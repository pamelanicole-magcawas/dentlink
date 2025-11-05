<?php
// db_connect.php - Main database connection file

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

// Create mysqli connection for files that use mysqli
$host = "localhost";
$db_name = "dental_clinic";
$username = "root";
$password = "";

try {
    $conn = new mysqli($host, $username, $password, $db_name);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}
?>