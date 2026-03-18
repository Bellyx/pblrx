<?php
include '../../../config/db.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM activities WHERE id=?");
$stmt->execute([$id]);

header("Location:activity_index.php");
include '../../../includes/header_db.php';