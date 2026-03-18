<?php
require_once 'config/db.php';
require_once 'includes/header.php';
$items=$pdo->query("
SELECT *
FROM portfolio
WHERE is_active=1
ORDER BY sort_order
")->fetchAll();
?>

<h1>Our Success</h1>

<div class="grid">

<?php foreach($items as $p): ?>

<a href="portfolio_detail.php?slug=<?= $p['slug'] ?>">

<div class="card">

<img src="assets/upload/portfolio/<?= $p['cover_image'] ?>">

<h3><?= $p['title_en'] ?></h3>

</div>

</a>

<?php endforeach ?>

</div>


<style>

.grid{
display:grid;
grid-template-columns:repeat(3,1fr);
gap:30px;
}

.card img{
width:100%;
height:250px;
object-fit:cover;
}

</style>