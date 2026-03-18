<?php
session_start();

// ล้าง session ทั้งหมด
$_SESSION = [];

// ทำลาย session
session_destroy();

// กลับไปหน้า login
header("Location: login.php");
exit;
