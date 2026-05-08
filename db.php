<?php
session_start();

$servername = "sql101.infinityfree.com";
$username = "if0_41854834";
$password = "akilliiksistemi"; 
$dbname = "if0_41854834_ik_sistemi";

// Create connection using MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable error reporting
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>
