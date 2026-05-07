<?php
// db_connect.php
// Veritabanı bağlantı ayarları (PDO kullanılarak güvenli bağlantı sağlanır)

$host = '127.0.0.1';
$db   = 'smarthr_db';
$user = 'root'; // Kendi MySQL kullanıcı adınızla değiştirin
$pass = '';     // Kendi MySQL şifrenizle değiştirin
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Hataları exception olarak fırlat
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Verileri associative array olarak getir
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Gerçek prepared statements kullan (SQL Injection koruması)
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Gerçek sistemlerde hata detayı kullanıcıya gösterilmez, loglanır
    die("Veritabanı bağlantı hatası: Kullanıcı adı, şifre veya veritabanı adını kontrol ediniz. (Hata: " . $e->getMessage() . ")");
}
?>
