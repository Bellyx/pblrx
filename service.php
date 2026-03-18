<?php
require_once 'config/db.php';
require_once 'includes/header_db.php';

$lang = $_SESSION['lang'] ?? 'th';

$stmt = $pdo->query("
SELECT *
FROM services
WHERE is_active=1
ORDER BY sort_order ASC
");

$services = $stmt->fetchAll();
?>

<style>

.services-grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
gap:30px;
margin-top:40px;
}

.service-card{
border:1px solid #eee;
padding:30px;
text-align:center;
border-radius:10px;
transition:.3s;
background:white;
}

.service-card:hover{
transform:translateY(-5px);
box-shadow:0 10px 30px rgba(0,0,0,.1);
}

.service-card img{
width:60px;
margin-bottom:15px;
}

.service-card h3{
font-size:20px;
}

</style>

<div class="container">

<h1 style="margin-top:40px">
<?= $lang=='th'?'บริการของเรา':'Our Services' ?>
</h1>

<div class="services-grid">

<?php foreach($services as $s): ?>

<a class="service-card"
href="/services/<?= $s['slug'] ?>">

<img src="/assets/icons/<?= $s['icon'] ?>">

<h3>
<?= $lang=='th'
? $s['title_th']
: $s['title_en'] ?>
</h3>

</a>

<?php endforeach ?>

</div>

</div>