<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['services_cache'])) {

    $stmt = $pdo->prepare("
        SELECT slug, title_th, title_en
        FROM services_dw
        WHERE is_active = 1
        ORDER BY sort_order ASC
    ");
    $stmt->execute();

    $_SESSION['services_cache'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$services = $_SESSION['services_cache'];
