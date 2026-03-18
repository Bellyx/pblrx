<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['th','en'])) {
  $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'] ?? 'th';

$T = require __DIR__ . "/../translate/{$lang}.php";
