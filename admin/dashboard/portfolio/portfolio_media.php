<?php
require_once __DIR__.'/../../../config/db.php';

$id=$_GET['id'];

/* upload image */

if(isset($_POST['upload_image'])){

$file=time().'_'.$_FILES['image']['name'];

move_uploaded_file(
$_FILES['image']['tmp_name'],
"../../../assets/upload/portfolio/".$file
);

$pdo->prepare("
INSERT INTO portfolio_media
(portfolio_id,media_type,file_path)
VALUES (?,?,?)
")->execute([$id,'image',$file]);

header("Location: portfolio_media.php?id=".$id);
exit;
}

/* youtube */

if(isset($_POST['add_video'])){

$pdo->prepare("
INSERT INTO portfolio_media
(portfolio_id,media_type,youtube_url)
VALUES (?,?,?)
")->execute([$id,'video',$_POST['youtube']]);

header("Location: portfolio_media.php?id=".$id);
exit;
}

/* delete */

if(isset($_GET['delete'])){
$pdo->prepare("DELETE FROM portfolio_media WHERE id=?")
->execute([$_GET['delete']]);
header("Location: portfolio_media.php?id=".$id);
exit;
}

$media=$pdo->prepare("
SELECT * FROM portfolio_media
WHERE portfolio_id=?
");
$media->execute([$id]);
$media=$media->fetchAll();
?>

<h2>Media Manager</h2>

<h3>Upload Image</h3>

<form method="post" enctype="multipart/form-data">
<input type="file" name="image">
<button name="upload_image">Upload</button>
</form>

<h3>Add Youtube</h3>

<form method="post">
<input name="youtube" placeholder="youtube embed link">
<button name="add_video">Add</button>
</form>

<hr>

<?php foreach($media as $m): ?>

<div style="margin-bottom:20px">

<?php if($m['media_type']=="image"): ?>

<img
src="../../assets/upload/portfolio/<?= $m['file_path'] ?>"
width="200">

<?php endif ?>

<?php if($m['media_type']=="video"): ?>

<iframe width="300"
src="<?= $m['youtube_url'] ?>">
</iframe>

<?php endif ?>

<br>

<a href="?id=<?= $id ?>&delete=<?= $m['id'] ?>">
Delete
</a>

</div>

<?php endforeach ?>