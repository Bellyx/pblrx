<?php
include '../../../config/db.php';
require_once '../../../includes/header_db.php';

/* ── AJAX: mark as read ── */
if (isset($_GET['mark_read'])) {
  $id = (int)$_GET['mark_read'];
  $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?")->execute([$id]);
  echo json_encode(['ok' => true]);
  exit;
}

/* ── AJAX: delete ── */
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$id]);
  header("Location: contact_index.php");
  exit;
}

$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")
               ->fetchAll(PDO::FETCH_ASSOC);

$total    = count($messages);
$unread   = count(array_filter($messages, fn($r) => empty($r['is_read'])));
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Messages – PBLR Backoffice</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="/PBLR/assets/css/backend/stylecontact.css" rel="stylesheet">
  
</head>
<body>

<div class="page">

  <!-- Header -->
  <div class="page-header">
    <div class="page-header-left">
      <h2>
        <span class="h-icon"><i class="bi bi-envelope"></i></span>
        ข้อความติดต่อ
      </h2>
      <p>รายการข้อความที่ส่งมาจากหน้าเว็บไซต์</p>
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-row">
    <div class="stat-chip">
      <i class="bi bi-inbox"></i>
      <span>ทั้งหมด <strong><?= $total ?></strong> ข้อความ</span>
    </div>
    <div class="stat-chip unread-chip">
      <i class="bi bi-envelope-fill"></i>
      <span>ยังไม่อ่าน <strong id="unreadCount"><?= $unread ?></strong> ข้อความ</span>
    </div>
  </div>

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="search-wrap">
      <i class="bi bi-search"></i>
      <input type="text" class="search-input" id="searchInput" placeholder="ค้นหาชื่อ, อีเมล, หัวข้อ...">
    </div>
    <select class="filter-select" id="filterStatus">
      <option value="all">ทุกสถานะ</option>
      <option value="unread">ยังไม่อ่าน</option>
      <option value="read">อ่านแล้ว</option>
    </select>
  </div>

  <!-- Table -->
  <div class="table-card">
    <div class="table-responsive">
      <table id="msgTable">
        <thead>
          <tr>
            <th style="width:44px">#</th>
            <th>ผู้ส่ง</th>
            <th>หัวข้อ</th>
            <th style="width:130px">วันที่</th>
            <th style="width:90px; text-align:center">สถานะ</th>
            <th style="width:120px; text-align:center">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($messages)): ?>
            <tr>
              <td colspan="6">
                <div class="empty-state">
                  <i class="bi bi-inbox"></i>
                  <p>ยังไม่มีข้อความติดต่อเข้ามา</p>
                </div>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($messages as $i => $row):
              $isRead = !empty($row['is_read']);
            ?>
              <tr class="<?= $isRead ? '' : 'unread' ?>"
                  data-id="<?= $row['id'] ?>"
                  data-read="<?= $isRead ? '1' : '0' ?>">

                <td style="color:var(--ink-soft);font-family:'DM Serif Display',serif">
                  <?= $i + 1 ?>
                </td>

                <td>
                  <div class="sender-name"><?= htmlspecialchars($row['name']) ?></div>
                  <div class="sender-email"><?= htmlspecialchars($row['email']) ?></div>
                </td>

                <td>
                  <div class="cell-subject" title="<?= htmlspecialchars($row['subject']) ?>">
                    <?= htmlspecialchars($row['subject'] ?: '(ไม่มีหัวข้อ)') ?>
                  </div>
                </td>

                <td>
                  <div class="cell-date">
                    <i class="bi bi-clock" style="font-size:.68rem"></i>
                    <?= date('d M Y', strtotime($row['created_at'])) ?>
                  </div>
                  <div style="font-size:.72rem;color:var(--ink-soft);font-weight:300;margin-top:2px">
                    <?= date('H:i น.', strtotime($row['created_at'])) ?>
                  </div>
                </td>

                <td style="text-align:center">
                  <span class="read-badge <?= $isRead ? 'read' : 'unread' ?>">
                    <span class="dot"></span>
                    <?= $isRead ? 'อ่านแล้ว' : 'ใหม่' ?>
                  </span>
                </td>

                <td>
                  <div class="actions">
                    <a href="view.php?id=<?= $row['id'] ?>"
                       class="action-btn view" title="ดูข้อความ">
                      <i class="bi bi-eye"></i>
                    </a>
                    <button
                      class="action-btn mark <?= $isRead ? 'done' : '' ?>"
                      title="<?= $isRead ? 'อ่านแล้ว' : 'ทำเครื่องหมายอ่านแล้ว' ?>"
                      onclick="markRead(this, <?= $row['id'] ?>)">
                      <i class="bi bi-check-all"></i>
                    </button>
                    <button
                      class="action-btn delete" title="ลบ"
                      onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['name'])) ?>')">
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

    <div class="table-footer">
      <span class="table-count">
        แสดง <strong id="visibleCount"><?= $total ?></strong>
        จาก <strong><?= $total ?></strong> ข้อความ
      </span>
    </div>
  </div>

</div>

<script>
  const allRows      = document.querySelectorAll('#msgTable tbody tr[data-id]');
  const visibleCount = document.getElementById('visibleCount');
  const unreadCount  = document.getElementById('unreadCount');

  /* ── Filter helper ── */
  function applyFilters() {
    const q      = document.getElementById('searchInput').value.toLowerCase().trim();
    const status = document.getElementById('filterStatus').value;
    let n = 0;
    allRows.forEach(tr => {
      const name    = tr.querySelector('.sender-name')?.textContent.toLowerCase() ?? '';
      const email   = tr.querySelector('.sender-email')?.textContent.toLowerCase() ?? '';
      const subject = tr.querySelector('.cell-subject')?.textContent.toLowerCase() ?? '';
      const isRead  = tr.dataset.read === '1';

      const matchQ  = !q || name.includes(q) || email.includes(q) || subject.includes(q);
      const matchS  = status === 'all'
                   || (status === 'unread' && !isRead)
                   || (status === 'read'   && isRead);

      const show = matchQ && matchS;
      tr.style.display = show ? '' : 'none';
      if (show) n++;
    });
    visibleCount.textContent = n;
  }

  document.getElementById('searchInput').addEventListener('input', applyFilters);
  document.getElementById('filterStatus').addEventListener('change', applyFilters);

  /* ── Mark as read (no reload) ── */
  function markRead(btn, id) {
    if (btn.classList.contains('done')) return;
    btn.disabled = true;
    fetch(`?mark_read=${id}`)
      .then(r => r.json())
      .then(() => {
        const tr    = btn.closest('tr');
        const badge = tr.querySelector('.read-badge');

        tr.classList.remove('unread');
        tr.dataset.read = '1';

        badge.className = 'read-badge read';
        badge.innerHTML = '<span class="dot"></span>อ่านแล้ว';

        btn.classList.add('done');
        btn.disabled = false;

        /* update unread counter */
        const cur = parseInt(unreadCount.textContent);
        if (cur > 0) unreadCount.textContent = cur - 1;
      });
  }

  /* ── Delete confirm ── */
  function confirmDelete(id, name) {
    Swal.fire({
      title: 'ลบข้อความนี้?',
      html: `<span style="font-size:.88rem;color:#6b6d85">จาก <b>${name}</b></span><br>
             <small style="color:#e05c5c">ไม่สามารถกู้คืนได้</small>`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#e05c5c',
      cancelButtonColor:  '#4a4c62',
      confirmButtonText:  'ลบเลย',
      cancelButtonText:   'ยกเลิก'
    }).then(r => {
      if (r.isConfirmed) location.href = '?delete=' + id;
    });
  }
</script>
</body>
</html>