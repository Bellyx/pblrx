<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// ✅ รับค่าจากฟอร์ม
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// ป้องกัน form ว่าง
if ($username === '' || $password === '') {
  header("Location: login.php?error=1");
  exit;
}

// ✅ query admin
$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
$stmt->execute([$username]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ❌ ไม่เจอ user
if (!$user) {
  header("Location: login.php?error=1");
  exit;
}

// ❌ password ไม่ตรง
if (!password_verify($password, $user['password'])) {
  header("Location: login.php?error=1");
  exit;
}

// ✅ login ผ่าน
$_SESSION['admin_id'] = $user['id'];
$_SESSION['admin_username'] = $user['username'];



header("Location: dashboard/ds_index.php");
exit;
