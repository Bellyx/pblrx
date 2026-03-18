<?php
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $title_th = $_POST['title_th'] ?? '';
  $title_en = $_POST['title_en'] ?? '';
  $icon     = $_POST['icon'] ?? '';

  if ($title_th && $title_en && $icon) {

    $stmt = $pdo->prepare("
      INSERT INTO services (title_th, title_en, icon)
      VALUES (:title_th, :title_en, :icon)
    ");

    $stmt->execute([
      ':title_th' => $title_th,
      ':title_en' => $title_en,
      ':icon'     => $icon
    ]);

    header("Location: services_add.php?success=1");
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>Add Service</title>
  <style>
    body { font-family: Arial; padding: 20px; }
    input, select, button { padding: 8px; margin: 5px 0; width: 300px; }
  </style>
</head>
<body>

<h2>เพิ่มบริการ</h2>

<?php if (isset($_GET['success'])): ?>
  <p style="color:green;">บันทึกข้อมูลเรียบร้อยแล้ว</p>
<?php endif; ?>

<form method="post">
  <input name="title_th" placeholder="ชื่อบริการ (TH)" required>
  <br>

  <input name="title_en" placeholder="Service name (EN)" required>
  <br>

  <select name="icon" required>
    <option value="">-- เลือกไอคอน --</option>
    <option value="research.svg">Research</option>
    <option value="analytics.svg">Analytics</option>
    <option value="consulting.svg">Consulting</option>
    <option value="academic.svg">Academic</option>
    <option value="media.svg">Media</option>
    <option value="training.svg">Training</option>
  </select>
  <br>

  <button type="submit">Save</button>
</form>

</body>
</html>