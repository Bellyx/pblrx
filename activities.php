<?php
include 'config/db.php';
require_once __DIR__ . '/includes/header.php';

$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page-1) * $limit;

$total = $pdo->query("SELECT COUNT(*) FROM activities WHERE status=1")->fetchColumn();
$totalPages = ceil($total / $limit);

$stmt = $pdo->prepare("
SELECT id,slug,title_th,description_th,image_path,created_at
FROM activities
WHERE status = 1
ORDER BY created_at DESC
LIMIT :limit OFFSET :offset
");

$stmt->bindValue(':limit',$limit,PDO::PARAM_INT);
$stmt->bindValue(':offset',$offset,PDO::PARAM_INT);
$stmt->execute();

$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="/PBLR/assets/css/fontend/styleact.css">

<section class="activities-section">

<div class="container">

<h2 class="text-center mb-5">กิจกรรมและข่าวสาร</h2>

<div class="activities-grid">

<?php foreach($activities as $row): ?>

<a 
href="activity-detail.php?slug=<?= urlencode($row['slug']) ?>"
target="_blank"
rel="noopener noreferrer"
class="activity-card"
>

<div class="activity-img">

<img 
loading="lazy"
src="assets/upload/activities/<?= $row['image_path'] ?: 'no-image.png' ?>"
alt="<?= htmlspecialchars($row['title_th']) ?>"
>

</div>

<div class="activity-content">

<div class="activity-date">
<?= date('d M Y',strtotime($row['created_at'])) ?>
</div>

<h3><?= htmlspecialchars($row['title_th']) ?></h3>

<p>
<?= mb_substr(strip_tags($row['description_th']),0,120) ?>...
</p>

<span class="activity-btn">
ดูรายละเอียด
</span>

</div>

</a>

<?php endforeach; ?>

</div>


<!-- pagination -->

<div class="pagination">

<?php for($i=1;$i<=$totalPages;$i++): ?>

<a 
href="?page=<?= $i ?>" 
class="<?= $i==$page ? 'active':'' ?>"
>
<?= $i ?>
</a>

<?php endfor; ?>

</div>

</div>

</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>