<?php
$conn = new mysqli("localhost","user","pass","dbname");

$name = $_POST['name'];
$position = $_POST['position'];

$imageName = null;
if (!empty($_FILES['image']['name'])) {
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $imageName = time() . rand(100,999) . '.' . $ext;
    move_uploaded_file(
        $_FILES['image']['tmp_name'],
        "../assets/uploads/personnel/" . $imageName
    );
}

$sql = "INSERT INTO personnel (name, position, image)
        VALUES ('$name', '$position', '$imageName')";
$conn->query($sql);

header("Location: personnel_list.php");
