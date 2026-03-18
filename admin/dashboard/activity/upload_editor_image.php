<?php

// $uploadPath = "../assets/upload/editor/";
$uploadPath = "pblr\assets\upload\editor";

if(!file_exists($uploadPath)){
mkdir($uploadPath,0777,true);
}

if(isset($_FILES['upload'])){

$ext = pathinfo($_FILES['upload']['name'],PATHINFO_EXTENSION);

$name = time().rand(100,999).".".$ext;

move_uploaded_file(
$_FILES['upload']['tmp_name'],
$uploadPath.$name
);

$url = "/assets/upload/editor/".$name;

echo json_encode([
"url"=>$url
]);

}