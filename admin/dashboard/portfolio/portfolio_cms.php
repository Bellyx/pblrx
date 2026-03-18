<?php
require_once __DIR__.'/../../../config/db.php';

function slug($t){
$t=strtolower($t);
$t=preg_replace('/[^a-z0-9]+/','-',$t);
return trim($t,'-');
}

if(isset($_POST['add'])){

$cover="";

if($_FILES['cover']['name']){

$cover=time()."_".$_FILES['cover']['name'];

move_uploaded_file(
$_FILES['cover']['tmp_name'],
"../../../assets/upload/portfolio/".$cover
);

}

$s=slug($_POST['title_en']);

$pdo->prepare("
INSERT INTO portfolio
(title_th,title_en,slug,cover_image,youtube_playlist)
VALUES (?,?,?,?,?)
")->execute([
$_POST['title_th'],
$_POST['title_en'],
$s,
$cover,
$_POST['playlist']
]);

header("Location: portfolio_cms.php");
}

$items=$pdo->query("SELECT * FROM portfolio")->fetchAll();
?>

<h2>Portfolio CMS</h2>

<form method="post" enctype="multipart/form-data">

<input name="title_th" placeholder="ชื่อไทย">

<input name="title_en" placeholder="English">

<input name="playlist" placeholder="Youtube playlist">

<input type="file" name="cover">

<button name="add">Add</button>

</form>

<hr>

<?php foreach($items as $i): ?>

<div>

<img src="/assets/upload/portfolio/<?= $i['cover_image'] ?>" width="150">

<?= $i['title_en'] ?>

<a href="portfolio_edit.php?id=<?= $i['id'] ?>">Edit</a>

</div>

<?php endforeach ?>