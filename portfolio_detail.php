<?php
require_once 'config/db.php';

$slug=$_GET['slug'];

$stmt=$pdo->prepare("
SELECT *
FROM portfolio
WHERE slug=?
");

$stmt->execute([$slug]);

$p=$stmt->fetch();

$media=$pdo->prepare("
SELECT *
FROM portfolio_media
WHERE portfolio_id=?
");

$media->execute([$p['id']]);
$media=$media->fetchAll();
?>

<h1><?= $p['title_en'] ?></h1>

<p><?= $p['description'] ?></p>


<?php if($p['youtube_playlist']): ?>

<h2>Videos</h2>

<iframe
width="100%"
height="500"
src="<?= $p['youtube_playlist'] ?>">
</iframe>

<?php endif ?>


<h2>Gallery</h2>

<div class="gallery">

<?php foreach($media as $m): ?>

<?php if($m['media_type']=="image"): ?>

<img src="assets/upload/portfolio/<?= $m['file_path'] ?>">

<?php endif ?>

<?php endforeach ?>

</div>


<h2>Certificates</h2>

<div class="cert">

<?php foreach($media as $m): ?>

<?php if($m['media_type']=="certificate"): ?>

<img src="assets/upload/portfolio/<?= $m['file_path'] ?>" width="200">

<?php endif ?>

<?php endforeach ?>

</div>


<style>

.gallery{
display:grid;
grid-template-columns:repeat(3,1fr);
gap:20px;
}

.gallery img{
width:100%;
}

.cert img{
margin:10px;
}

</style>