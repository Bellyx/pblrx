<?php
include '../../../config/db.php';
require_once '../../../includes/header_db.php';
if($_POST){

$title_th = $_POST['title_th'];
$title_en = $_POST['title_en'];
$description_th = $_POST['description_th'];
$description_en = $_POST['description_en'];

$image = '';

if(!empty($_FILES['image']['name'])){

$ext = pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION);

$image = time().'.'.$ext;

move_uploaded_file(
$_FILES['image']['tmp_name'],
"../../assets/upload/activities/".$image
);

}

$stmt = $pdo->prepare("
INSERT INTO activities
(title_th,title_en,description_th,description_en,image_path,created_at)
VALUES(?,?,?,?,?,NOW())
");

$stmt->execute([
$title_th,
$title_en,
$description_th,
$description_en,
$image
]);

header("Location: activities_list.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form method="post" enctype="multipart/form-data">

<input name="title_th" placeholder="Title TH">

<input name="title_en" placeholder="Title EN">

<textarea name="description_th"></textarea>

<textarea name="description_en"></textarea>

<input type="file" name="image">

<button type="submit">Save</button>

</form>
</body>
</html>