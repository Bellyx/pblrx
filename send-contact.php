<?php
include 'config/db.php';

$name = $_POST['name'];
$email = $_POST['email'];
$subject = $_POST['subject'];
$message = $_POST['message'];

$stmt = $pdo->prepare("
INSERT INTO contact_messages
(name,email,subject,message)
VALUES (?,?,?,?)
");

$stmt->execute([
$name,
$email,
$subject,
$message
]);

header("Location: contact.php?success=1");