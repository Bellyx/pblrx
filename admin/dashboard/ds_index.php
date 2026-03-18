<?php
require_once '../auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once '../../includes/header_db.php';

/* ── Stats ── */
$serviceCount    = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
$activityCount   = $pdo->query("SELECT COUNT(*) FROM activities")->fetchColumn();
$messageCount    = $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$newMessageCount = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();

/* ── Latest Activities ── */
$latestActivities = $pdo->query("
  SELECT id, title_th, created_at FROM activities
  ORDER BY created_at DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

/* ── Latest Messages ── */
$latestMessages = $pdo->query("
  SELECT id, name, subject, created_at, is_read FROM contact_messages
  ORDER BY created_at DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard – PBLR Backoffice</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="/PBLR/assets/css/backend/styledsindex.css" rel="stylesheet">
</head>
<body>

<div class="main-content">

  <!-- ══ TOP BAR ══ -->
  <div class="topbar">
    <div class="topbar-left">
      <h1 class="topbar-title">
        <span class="topbar-dot"></span>
        Dashboard
      </h1>
      <span class="topbar-date">
        <i class="bi bi-calendar3"></i>
        <?= date('d M Y') ?>
      </span>
    </div>

    <div class="topbar-right">

      <!-- Notification bell -->
      <div class="notif-wrap" id="notifWrap">
        <button class="notif-btn" id="notifBtn">
          <i class="bi bi-bell"></i>
          <?php if ($newMessageCount > 0): ?>
            <span class="notif-badge"><?= $newMessageCount ?></span>
          <?php endif; ?>
        </button>

        <div class="notif-dropdown" id="notifDropdown">
          <div class="notif-header">
            <span>ข้อความใหม่</span>
            <?php if ($newMessageCount > 0): ?>
              <span class="badge-count"><?= $newMessageCount ?></span>
            <?php endif; ?>
          </div>

          <?php if (empty($latestMessages)): ?>
            <div class="notif-empty">
              <i class="bi bi-inbox"></i>
              ไม่มีข้อความใหม่
            </div>
          <?php else: ?>
            <?php foreach ($latestMessages as $m): ?>
              <a href="contact/view.php?id=<?= $m['id'] ?>" class="notif-item <?= $m['is_read'] ? '' : 'unread' ?>">
                <div class="notif-avatar"><?= mb_strtoupper(mb_substr($m['name'],0,1)) ?></div>
                <div class="notif-body">
                  <div class="notif-name"><?= htmlspecialchars($m['name']) ?></div>
                  <div class="notif-subject"><?= htmlspecialchars($m['subject'] ?: '(ไม่มีหัวข้อ)') ?></div>
                </div>
                <div class="notif-time"><?= date('d M', strtotime($m['created_at'])) ?></div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>

          <a href="contact/contact_index.php" class="notif-footer">
            ดูข้อความทั้งหมด <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>

      <a href="../logout.php" class="btn-logout">
        <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
      </a>
    </div>
  </div>

  <!-- ══ STAT CARDS ══ -->
  <div class="stat-grid">

    <a href="service/service_index.php" class="stat-card">
      <div class="stat-icon" style="background:rgba(200,169,74,0.12);border-color:rgba(200,169,74,0.22)">
        <i class="bi bi-briefcase" style="color:#c8a94a"></i>
      </div>
      <div class="stat-body">
        <div class="stat-num"><?= $serviceCount ?></div>
        <div class="stat-label">บริการทั้งหมด</div>
      </div>
      <i class="bi bi-arrow-up-right stat-arrow"></i>
    </a>

    <a href="activity/activity_index.php" class="stat-card">
      <div class="stat-icon" style="background:rgba(59,130,246,0.1);border-color:rgba(59,130,246,0.2)">
        <i class="bi bi-calendar-event" style="color:#3b82f6"></i>
      </div>
      <div class="stat-body">
        <div class="stat-num"><?= $activityCount ?></div>
        <div class="stat-label">กิจกรรมทั้งหมด</div>
      </div>
      <i class="bi bi-arrow-up-right stat-arrow"></i>
    </a>

    <a href="contact/contact_index.php" class="stat-card">
      <div class="stat-icon" style="background:rgba(61,184,138,0.1);border-color:rgba(61,184,138,0.2)">
        <i class="bi bi-envelope" style="color:#3db88a"></i>
      </div>
      <div class="stat-body">
        <div class="stat-num"><?= $messageCount ?></div>
        <div class="stat-label">ข้อความทั้งหมด</div>
      </div>
      <i class="bi bi-arrow-up-right stat-arrow"></i>
    </a>

    <a href="contact/contact_index.php?filter=unread" class="stat-card <?= $newMessageCount > 0 ? 'stat-card--alert' : '' ?>">
      <div class="stat-icon" style="background:rgba(224,92,92,0.09);border-color:rgba(224,92,92,0.2)">
        <i class="bi bi-envelope-fill" style="color:#e05c5c"></i>
      </div>
      <div class="stat-body">
        <div class="stat-num" style="color:#e05c5c"><?= $newMessageCount ?></div>
        <div class="stat-label">ยังไม่ได้อ่าน</div>
      </div>
      <?php if ($newMessageCount > 0): ?>
        <span class="stat-pulse"></span>
      <?php endif; ?>
    </a>

  </div>

  <!-- ══ TABLES ROW ══ -->
  <div class="tables-row">

    <!-- Latest Messages -->
    <div class="table-card">
      <div class="table-card-header">
        <div class="table-card-title">
          <i class="bi bi-envelope"></i>
          ข้อความล่าสุด
        </div>
        <a href="contact/contact_index.php" class="table-card-link">
          ดูทั้งหมด <i class="bi bi-arrow-right"></i>
        </a>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ผู้ส่ง</th>
              <th>หัวข้อ</th>
              <th style="width:90px">วันที่</th>
              <th style="width:70px;text-align:center">สถานะ</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($latestMessages)): ?>
              <tr><td colspan="4" class="empty-cell">ยังไม่มีข้อความ</td></tr>
            <?php else: ?>
              <?php foreach ($latestMessages as $m): ?>
                <tr class="<?= $m['is_read'] ? '' : 'row-unread' ?>"
                    onclick="location.href='contact/view.php?id=<?= $m['id'] ?>'"
                    style="cursor:pointer">
                  <td>
                    <div class="cell-name"><?= htmlspecialchars($m['name']) ?></div>
                  </td>
                  <td>
                    <div class="cell-subject"><?= htmlspecialchars($m['subject'] ?: '(ไม่มีหัวข้อ)') ?></div>
                  </td>
                  <td class="cell-date"><?= date('d M Y', strtotime($m['created_at'])) ?></td>
                  <td style="text-align:center">
                    <span class="pill <?= $m['is_read'] ? 'pill-read' : 'pill-new' ?>">
                      <?= $m['is_read'] ? 'อ่าน' : 'ใหม่' ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Latest Activities -->
    <div class="table-card">
      <div class="table-card-header">
        <div class="table-card-title">
          <i class="bi bi-calendar-event"></i>
          กิจกรรมล่าสุด
        </div>
        <a href="activity/activity_index.php" class="table-card-link">
          ดูทั้งหมด <i class="bi bi-arrow-right"></i>
        </a>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ชื่อกิจกรรม</th>
              <th style="width:110px">วันที่สร้าง</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($latestActivities)): ?>
              <tr><td colspan="2" class="empty-cell">ยังไม่มีกิจกรรม</td></tr>
            <?php else: ?>
              <?php foreach ($latestActivities as $a): ?>
                <tr onclick="location.href='activity/activity_index.php'" style="cursor:pointer">
                  <td>
                    <div class="cell-name"><?= htmlspecialchars($a['title_th']) ?></div>
                  </td>
                  <td class="cell-date"><?= date('d M Y', strtotime($a['created_at'])) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

</div><!-- /.main-content -->

<script>
  /* Notification dropdown toggle */
  const btn      = document.getElementById('notifBtn');
  const dropdown = document.getElementById('notifDropdown');

  btn.addEventListener('click', e => {
    e.stopPropagation();
    dropdown.classList.toggle('show');
  });

  document.addEventListener('click', () => dropdown.classList.remove('show'));
  dropdown.addEventListener('click', e => e.stopPropagation());
</script>
</body>
</html>