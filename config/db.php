<?php
/* ===============================
   DATABASE CONFIG
================================ */
$host = 'localhost';
$dbname = 'pblr';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

/* ===============================
   PDO (ใช้กับหน้าเว็บ / editor)
================================ */
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("PDO Connection failed: " . $e->getMessage());
}

/* ===============================
   MySQLi (ใช้กับ Admin CRUD)
================================ */
$db = new mysqli($host, $user, $pass, $dbname);
if ($db->connect_error) {
    die("MySQLi Connection failed: " . $db->connect_error);
}
$db->set_charset("utf8mb4");

/* alias เผื่อบางไฟล์ใช้ $conn */
$conn = $db;
