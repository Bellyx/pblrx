<?php
require_once '../auth.php';
require_once __DIR__ . '/../../config/db.php';

require_once '../../includes/header_db.php';
/* ===== ดึงข้อมูลจริง ===== */

// จำนวนบริการ
$serviceCount = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();

// จำนวนกิจกรรม
$activityCount = $pdo->query("SELECT COUNT(*) FROM activities")->fetchColumn();

// กิจกรรมล่าสุด 5 รายการ
$latestActivities = $pdo->query("
  SELECT id, title_th, created_at
  FROM activities
  ORDER BY created_at DESC
  LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="/PBLR/assets/css/style_db.css" rel="stylesheet">
  
</head>

<body>



  <div class="main">

    <div class="topbar">
      <strong>Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?></strong>

      <div class="topbar-right">
        <span class="date"><?= date('d M Y') ?></span>
        <a href="../logout.php" class="btn-logout">Logout</a>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="cards">
      <div class="card">
        <h3>Services</h3>
        <p><?= $serviceCount ?></p>
      </div>
      <div class="card">
        <h3>Activities</h3>
        <p><?= $activityCount ?></p>
      </div>
    </div>

    <!-- Latest Activities -->
    <div class="table">
      <table>
        <thead>
          <tr>
            <th>Activity</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($latestActivities as $a): ?>
            <tr>
              <td><?= htmlspecialchars($a['title_th']) ?></td>
              <td><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>

          <?php if (empty($latestActivities)): ?>
            <tr>
              <td colspan="2">ยังไม่มีกิจกรรม</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>

</body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
document.querySelectorAll('.menu-toggle').forEach(menu => {
    menu.addEventListener('click', e => {
        e.preventDefault();

        menu.classList.toggle('open');
        const submenu = menu.nextElementSibling;
        submenu.classList.toggle('show');
    });
});
</script>
</html>