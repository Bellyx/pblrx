<?php
include '../../../config/db.php';


/* ── AJAX: toggle status ── */
if (isset($_GET['toggle_id'])) {
  $id = (int)$_GET['toggle_id'];
  $pdo->query("UPDATE activities SET status = 1 - status WHERE id = $id");
  $row = $pdo->query("SELECT status FROM activities WHERE id = $id")->fetch();
  echo json_encode(['status' => (int)$row['status']]);
  exit;
}

$stmt = $pdo->query("SELECT * FROM activities ORDER BY created_at DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
require_once '../../../includes/header_db.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Activities – PBLR Backoffice</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="/PBLR/assets/css/backend/styleactindex.css" rel="stylesheet">
</head>
<body>

<div class="page">

  <!-- Header -->
  <div class="page-header">
    <div class="page-header-left">
      <h2>
        <span class="h-icon"><i class="bi bi-calendar-event"></i></span>
        กิจกรรมทั้งหมด
      </h2>
      <p>จัดการเนื้อหากิจกรรมที่แสดงบนหน้าเว็บไซต์</p>
    </div>
    <a href="add.php" class="btn-add">
      <i class="bi bi-plus-lg"></i> เพิ่มกิจกรรม
    </a>
  </div>

  <!-- Stats -->
  <div class="stats-row">
    <?php
      $totalRows  = count($rows);
      $activeRows = count(array_filter($rows, fn($r) => ($r['status'] ?? 1) == 1));
      $thisMonth  = count(array_filter($rows, fn($r) =>
        date('Y-m', strtotime($r['created_at'])) === date('Y-m')
      ));
    ?>
    <div class="stat-chip">
      <i class="bi bi-stack"></i>
      <span>ทั้งหมด <strong><?= $totalRows ?></strong> รายการ</span>
    </div>
    <div class="stat-chip">
      <i class="bi bi-eye"></i>
      <span>กำลังแสดง <strong id="activeCount"><?= $activeRows ?></strong> รายการ</span>
    </div>
    <div class="stat-chip">
      <i class="bi bi-calendar-check"></i>
      <span>เดือนนี้ <strong><?= $thisMonth ?></strong> รายการ</span>
    </div>
  </div>

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="search-wrap">
      <i class="bi bi-search"></i>
      <input type="text" class="search-input" id="searchInput" placeholder="ค้นหาชื่อกิจกรรม...">
    </div>
  </div>

  <!-- Table -->
  <div class="table-card">
    <div class="table-responsive">
      <table id="activityTable">
        <thead>
          <tr>
            <th style="width:48px">#</th>
            <th style="width:80px">รูป</th>
            <th>ชื่อกิจกรรม</th>
            <th style="width:130px">วันที่สร้าง</th>
            <th style="width:90px; text-align:center">สถานะ</th>
            <th style="width:120px; text-align:center">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rows)): ?>
            <tr>
              <td colspan="6">
                <div class="empty-state">
                  <i class="bi bi-calendar-x"></i>
                  <p>ยังไม่มีข้อมูลกิจกรรม<br>กดปุ่ม "เพิ่มกิจกรรม" เพื่อเริ่มต้น</p>
                </div>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($rows as $i => $row):
              $status = (int)($row['status'] ?? 1);
            ?>
              <tr data-id="<?= $row['id'] ?>">

                <td><span class="cell-id"><?= $i + 1 ?></span></td>

                <td>
                  <img
                    class="activity-thumb"
                    src="../../../assets/upload/activities/<?= htmlspecialchars($row['image_path']) ?>"
                    alt="<?= htmlspecialchars($row['title_th']) ?>"
                    onerror="this.src='../../../assets/upload/no-image.png'"
                  >
                </td>

                <td>
                  <div class="cell-title"><?= htmlspecialchars($row['title_th']) ?></div>
                  <?php if (!empty($row['title_en'])): ?>
                    <div class="cell-title-en"><?= htmlspecialchars($row['title_en']) ?></div>
                  <?php endif; ?>
                </td>

                <td>
                  <div class="cell-date">
                    <i class="bi bi-clock"></i>
                    <?= date('d M Y', strtotime($row['created_at'])) ?>
                  </div>
                </td>

                <!-- Status badge -->
                <td style="text-align:center">
                  <span class="status-badge <?= $status ? 'on' : 'off' ?>">
                    <span class="dot"></span>
                    <?= $status ? 'แสดง' : 'ซ่อน' ?>
                  </span>
                </td>

                <!-- Actions -->
                <td>
                  <div class="actions">
                    <!-- View -->
                    <a href="view.php?id=<?= $row['id'] ?>"
                       class="action-btn view" title="ดูรายละเอียด">
                      <i class="bi bi-eye"></i>
                    </a>

                    <!-- Edit -->
                    <a href="activity_form.php?id=<?= $row['id'] ?>"
                       class="action-btn edit" title="แก้ไข">
                      <i class="bi bi-pencil"></i>
                    </a>

                    <!-- Toggle publish -->
                    <button
                      class="action-btn toggle <?= $status ? 'published' : 'hidden' ?>"
                      title="<?= $status ? 'ซ่อนโพส' : 'เผยแพร่โพส' ?>"
                      onclick="toggleStatus(this, <?= $row['id'] ?>)">
                      <i class="bi <?= $status ? 'bi-toggle-on' : 'bi-toggle-off' ?>"></i>
                    </button>

                    <!-- Delete -->
                    <button
                      class="action-btn delete" title="ลบ"
                      onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['title_th'])) ?>')">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>

              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Table footer -->
    <div class="table-footer">
      <span class="table-count">
        แสดง <strong id="visibleCount"><?= count($rows) ?></strong>
        จาก <strong><?= count($rows) ?></strong> รายการ
      </span>
    </div>
  </div>

</div>

<script>
/* ── Search (no reload) ── */
const searchInput  = document.getElementById('searchInput');
const allRows      = document.querySelectorAll('#activityTable tbody tr[data-id]');
const visibleCount = document.getElementById('visibleCount');

searchInput.addEventListener('input', () => {
  const q = searchInput.value.toLowerCase().trim();
  let n = 0;
  allRows.forEach(tr => {
    const th = tr.querySelector('.cell-title')?.textContent.toLowerCase() ?? '';
    const en = tr.querySelector('.cell-title-en')?.textContent.toLowerCase() ?? '';
    const ok = !q || th.includes(q) || en.includes(q);
    tr.style.display = ok ? '' : 'none';
    if (ok) n++;
  });
  visibleCount.textContent = n;
});

/* ── Toggle publish (no reload) ── */
function toggleStatus(btn, id) {
  btn.disabled = true;
  btn.style.opacity = '0.5';

  fetch(`?toggle_id=${id}`)
    .then(r => r.json())
    .then(data => {
      const isOn    = data.status === 1;
      const tr      = btn.closest('tr');
      const badge   = tr.querySelector('.status-badge');
      const dot     = badge.querySelector('.dot');
      const icon    = btn.querySelector('i');

      /* update badge */
      badge.className = `status-badge ${isOn ? 'on' : 'off'}`;
      badge.innerHTML = `<span class="dot"></span>${isOn ? 'แสดง' : 'ซ่อน'}`;

      /* update button */
      btn.className = `action-btn toggle ${isOn ? 'published' : 'hidden'}`;
      btn.title     = isOn ? 'ซ่อนโพส' : 'เผยแพร่โพส';
      icon.className = `bi ${isOn ? 'bi-toggle-on' : 'bi-toggle-off'}`;

      /* update active count chip */
      const activeEl = document.getElementById('activeCount');
      let cur = parseInt(activeEl.textContent);
      activeEl.textContent = isOn ? cur + 1 : cur - 1;
    })
    .catch(() => {
      Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถเปลี่ยนสถานะได้' });
    })
    .finally(() => {
      btn.disabled = false;
      btn.style.opacity = '';
    });
}

/* ── Delete (no reload unless confirmed) ── */
function confirmDelete(id, title) {
  Swal.fire({
    title: 'ลบกิจกรรมนี้?',
    html: `<span style="font-size:.88rem;color:#6b6d85">"${title}"</span><br>
           <small style="color:#e05c5c">ไม่สามารถกู้คืนได้</small>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#e05c5c',
    cancelButtonColor:  '#4a4c62',
    confirmButtonText:  'ลบเลย',
    cancelButtonText:   'ยกเลิก'
  }).then(r => {
    if (r.isConfirmed) location.href = 'delete.php?id=' + id;
  });
}
</script>
</body>
</html>