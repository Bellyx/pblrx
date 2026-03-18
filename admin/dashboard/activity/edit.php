<?php
include '../../../config/db.php';

$id = $_GET['id'] ?? 0;

/* ===== UPDATE DATA ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title_th = $_POST['title_th'];
    $title_en = $_POST['title_en'];
    $desc_th  = $_POST['description_th'];
    $desc_en  = $_POST['description_en'];

    // ดึงรูปเดิม
    $stmt = $pdo->prepare("SELECT image_path FROM activities WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $image = $row['image_path'];

    if (!empty($_FILES['image']['name'])) {

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

        $image = time() . "." . $ext;

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            "../../../assets/uploads/activities/" . $image
        );
    }

    $stmt = $pdo->prepare("
        UPDATE activities
        SET title_th=?, title_en=?, description_th=?, description_en=?, image_path=?
        WHERE id=?
    ");

    $stmt->execute([
        $title_th,
        $title_en,
        $desc_th,
        $desc_en,
        $image,
        $id
    ]);

    header("Location: activity_index.php");
    exit;
}

/* ===== GET DATA ===== */

$stmt = $pdo->prepare("SELECT * FROM activities WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

require_once '../../../includes/header_db.php';
?>

<div class="container mt-4">

<h3>Edit Activity</h3>

<form method="post" enctype="multipart/form-data">

<input
name="title_th"
value="<?= htmlspecialchars($row['title_th']) ?>"
class="form-control mb-3"
placeholder="Title TH">

<input
name="title_en"
value="<?= htmlspecialchars($row['title_en']) ?>"
class="form-control mb-3"
placeholder="Title EN">

<textarea
name="description_th"
class="form-control mb-3"
rows="5"><?= htmlspecialchars($row['description_th']) ?></textarea>

<textarea
name="description_en"
class="form-control mb-3"
rows="5"><?= htmlspecialchars($row['description_en']) ?></textarea>

<?php if($row['image_path']){ ?>

<img
src="/PBLR/assets/uploads/activities/<?= $row['image_path'] ?>"
width="150"
class="mb-3">

<?php } ?>

<input type="file" name="image" class="form-control mb-3">

<button class="btn btn-primary">
Update
</button>

<a href="activity_index.php" class="btn btn-secondary">
Back
</a>

</form>

</div>

</body>
</html>