<?php
session_start();

$servername = "localhost";
$username = "root";
$password = ""; // Your MySQL password if any
$dbname = "akilli_ik";

// Create connection using MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable error reporting
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>
