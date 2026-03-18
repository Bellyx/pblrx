<?php
session_start();
include '../../config/db.php';


if ($_POST) {
$filename = null;
if (!empty($_FILES['image']['name'])) {
$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$filename = 'act_' . time() . '.' . $ext;
move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/activities/' . $filename);
}


$stmt = $pdo->prepare("INSERT INTO activities(title_th,title_en,description_th,description_en,image_path)
VALUES (?,?,?,?,?)");
$stmt->execute([
$_POST['title_th'],
$_POST['title_en'],
$_POST['description_th'],
$_POST['description_en'],
$filename
]);


header('Location: activities.php');
}