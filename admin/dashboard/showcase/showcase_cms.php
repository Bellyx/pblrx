<?php
require_once __DIR__ . '/../../../config/db.php';

/* ADD CATEGORY */
if (isset($_POST['add_cat'])) {
    $pdo->prepare("
        INSERT INTO showcase_categories (title_th, title_en, sort_order)
        VALUES (?,?,?)
    ")->execute([$_POST['title_th'], $_POST['title_en'], $_POST['sort']]);
    header("Location: showcase_cms.php");
    exit;
}

/* TOGGLE STATUS */
if (isset($_GET['toggle'])) {
    $pdo->prepare("UPDATE showcase_categories SET is_active = 1 - is_active WHERE id=?")
        ->execute([$_GET['toggle']]);
    header("Location: showcase_cms.php");
    exit;
}

/* DELETE */
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM showcase_categories WHERE id=?")
        ->execute([$_GET['delete']]);
    header("Location: showcase_cms.php");
    exit;
}

$cats = $pdo->query("SELECT * FROM showcase_categories ORDER BY sort_order")->fetchAll();
require_once '../../../includes/header_db.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Showcase CMS – PBLR Backoffice</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Noto+Sans+Thai:wght@300;400;500;600&display=swap');

    :root {
      --night:       #0d0f1a;
      --night-2:     #141628;
      --surface:     #f0f2f5;
      --white:       #ffffff;
      --gold:        #c8a94a;
      --gold-pale:   #e6d49a;
      --gold-glow:   rgba(200,169,74,0.12);
      --gold-border: rgba(200,169,74,0.22);
      --ink:         #1a1c2e;
      --ink-mid:     #4a4c62;
      --ink-soft:    rgba(26,28,46,0.42);
      --danger:      #e05c5c;
      --danger-bg:   rgba(224,92,92,0.09);
      --success:     #3db88a;
      --success-bg:  rgba(61,184,138,0.09);
      --warn:        #e0993a;
      --warn-bg:     rgba(224,153,58,0.1);
      --teal:        #1b7fa8;
      --teal-bg:     rgba(27,127,168,0.09);
      --radius:      12px;
      --radius-sm:   8px;
      --shadow-sm:   0 2px 12px rgba(13,15,26,0.07);
      --shadow-md:   0 8px 32px rgba(13,15,26,0.11);
      --ease:        cubic-bezier(0.4,0,0.2,1);
      --dur:         0.22s;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Noto Sans Thai', sans-serif;
      background: var(--surface);
      color: var(--ink);
      min-height: 100vh;
    }

    .page-wrap {
      max-width: 960px;
      margin: 0 auto;
      padding: 2.5rem 2rem 5rem;
    }

    /* ── Page Header ── */
    .page-header {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 2rem;
      flex-wrap: wrap;
    }

    .page-header-left h2 {
      font-family: 'DM Serif Display', serif;
      font-size: 1.55rem;
      color: var(--ink);
      margin-bottom: 0.25rem;
    }

    .page-header-left p {
      font-size: 0.82rem;
      color: var(--ink-soft);
      font-weight: 300;
    }

    /* ── Add Form Card ── */
    .form-card {
      background: var(--white);
      border-radius: var(--radius);
      border: 1px solid rgba(26,28,46,0.07);
      box-shadow: var(--shadow-sm);
      overflow: hidden;
      margin-bottom: 2rem;
    }

    .form-card-header {
      background: var(--night);
      border-bottom: 1px solid var(--gold-border);
      padding: 1rem 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.65rem;
    }

    .form-card-header i {
      font-size: 0.8rem;
      color: var(--gold);
      background: var(--gold-glow);
      border: 1px solid var(--gold-border);
      width: 28px; height: 28px;
      border-radius: 7px;
      display: flex; align-items: center; justify-content: center;
    }

    .form-card-header span {
      font-family: 'DM Serif Display', serif;
      font-size: 0.95rem;
      color: #fff;
    }

    .form-card-body {
      padding: 1.5rem;
    }

    .form-row {
      display: flex;
      gap: 0.75rem;
      flex-wrap: wrap;
      align-items: flex-end;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 0.35rem;
      flex: 1;
      min-width: 140px;
    }

    .form-group label {
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: var(--ink-soft);
    }

    .form-group input {
      border: 1px solid rgba(26,28,46,0.12);
      border-radius: var(--radius-sm);
      padding: 0.6rem 0.85rem;
      font-family: 'Noto Sans Thai', sans-serif;
      font-size: 0.87rem;
      color: var(--ink);
      background: var(--white);
      transition: border-color var(--dur), box-shadow var(--dur);
      outline: none;
    }

    .form-group input:focus {
      border-color: var(--gold);
      box-shadow: 0 0 0 3px var(--gold-glow);
    }

    .form-group input::placeholder { color: rgba(26,28,46,0.28); }

    .form-group.sort { max-width: 100px; }

    .btn-add {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      background: var(--night);
      color: #fff;
      font-family: 'Noto Sans Thai', sans-serif;
      font-size: 0.85rem;
      font-weight: 600;
      padding: 0.62rem 1.4rem;
      border-radius: var(--radius-sm);
      border: none;
      cursor: pointer;
      transition: background var(--dur), transform var(--dur);
      white-space: nowrap;
      height: fit-content;
    }

    .btn-add:hover { background: var(--gold); color: var(--night); transform: translateY(-1px); }

    /* ── Table Card ── */
    .table-card {
      background: var(--white);
      border-radius: var(--radius);
      border: 1px solid rgba(26,28,46,0.07);
      box-shadow: var(--shadow-sm);
      overflow: hidden;
    }

    .table-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1.1rem 1.5rem;
      border-bottom: 1px solid rgba(26,28,46,0.06);
      background: var(--white);
    }

    .table-card-header-left {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      font-family: 'DM Serif Display', serif;
      font-size: 1rem;
      color: var(--ink);
    }

    .table-card-header-left i { color: var(--gold); }

    .count-badge {
      font-family: 'Noto Sans Thai', sans-serif;
      font-size: 0.72rem;
      font-weight: 600;
      background: var(--gold-glow);
      border: 1px solid var(--gold-border);
      color: var(--gold);
      padding: 0.15rem 0.6rem;
      border-radius: 100px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-family: 'Noto Sans Thai', sans-serif;
    }

    thead th {
      background: #fafaf9;
      font-size: 0.68rem;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: var(--ink-soft);
      padding: 0.85rem 1.25rem;
      border-bottom: 1px solid rgba(26,28,46,0.07);
      white-space: nowrap;
    }

    tbody tr {
      border-bottom: 1px solid rgba(26,28,46,0.045);
      transition: background var(--dur);
    }

    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #fdfcfa; }

    td {
      padding: 0.85rem 1.25rem;
      font-size: 0.875rem;
      color: var(--ink-mid);
      vertical-align: middle;
    }

    /* Sort order chip */
    .sort-chip {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 28px; height: 28px;
      border-radius: 6px;
      background: rgba(26,28,46,0.05);
      font-size: 0.78rem;
      font-weight: 600;
      color: var(--ink-soft);
    }

    /* Title cell */
    .cell-title-th {
      font-weight: 600;
      color: var(--ink);
      font-size: 0.9rem;
      margin-bottom: 0.1rem;
    }

    .cell-title-en {
      font-size: 0.76rem;
      color: var(--ink-soft);
      font-weight: 300;
    }

    /* Status badge */
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
      font-size: 0.72rem;
      font-weight: 600;
      padding: 0.22rem 0.65rem;
      border-radius: 100px;
    }

    .status-badge.on  { background: var(--success-bg); color: var(--success); }
    .status-badge.off { background: rgba(26,28,46,0.06); color: var(--ink-soft); }
    .status-badge .dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

    /* Action buttons */
    .actions {
      display: flex;
      gap: 0.35rem;
      align-items: center;
    }

    .act-btn {
      width: 32px; height: 32px;
      border-radius: var(--radius-sm);
      border: 1px solid transparent;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.82rem;
      cursor: pointer;
      background: transparent;
      transition: background var(--dur), color var(--dur), border-color var(--dur), transform var(--dur);
      text-decoration: none;
    }

    .act-btn:hover { transform: translateY(-1px); }

    .act-btn.images {
      background: var(--teal-bg);
      color: var(--teal);
      border-color: rgba(27,127,168,0.2);
    }
    .act-btn.images:hover { background: var(--teal); color: #fff; border-color: var(--teal); }

    .act-btn.toggle-on {
      background: var(--success-bg);
      color: var(--success);
      border-color: rgba(61,184,138,0.2);
    }
    .act-btn.toggle-on:hover { background: var(--success); color: #fff; border-color: var(--success); }

    .act-btn.toggle-off {
      background: rgba(26,28,46,0.06);
      color: var(--ink-soft);
      border-color: rgba(26,28,46,0.1);
    }
    .act-btn.toggle-off:hover { background: var(--ink); color: #fff; border-color: var(--ink); }

    .act-btn.edit {
      background: var(--warn-bg);
      color: var(--warn);
      border-color: rgba(224,153,58,0.2);
    }
    .act-btn.edit:hover { background: var(--warn); color: #fff; border-color: var(--warn); }

    .act-btn.del {
      background: var(--danger-bg);
      color: var(--danger);
      border-color: rgba(224,92,92,0.2);
    }
    .act-btn.del:hover { background: var(--danger); color: #fff; border-color: var(--danger); }

    /* Empty state */
    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      color: var(--ink-soft);
    }
    .empty-state i { font-size: 2.2rem; margin-bottom: 0.75rem; opacity: 0.25; display: block; }
    .empty-state p { font-size: 0.88rem; font-weight: 300; }

    @media (max-width: 640px) {
      .page-wrap { padding: 1.5rem 1rem 3rem; }
      .form-row { flex-direction: column; }
      .form-group.sort { max-width: 100%; }
    }
  </style>
</head>
<body>
<div class="page-wrap">

  <!-- Page Header -->
  <div class="page-header">
    <div class="page-header-left">
      <h2>Showcase CMS</h2>
      <p>จัดการหมวดหมู่และรูปภาพ Showcase</p>
    </div>
  </div>

  <!-- Add Form -->
  <div class="form-card">
    <div class="form-card-header">
      <i class="bi bi-plus-lg"></i>
      <span>เพิ่มหมวดหมู่ใหม่</span>
    </div>
    <div class="form-card-body">
      <form method="post">
        <div class="form-row">
          <div class="form-group">
            <label>ชื่อภาษาไทย</label>
            <input name="title_th" placeholder="เช่น งานวิจัยเกษตร" required>
          </div>
          <div class="form-group">
            <label>English Title</label>
            <input name="title_en" placeholder="e.g. Agricultural Research">
          </div>
          <div class="form-group sort">
            <label>ลำดับ</label>
            <input name="sort" type="number" placeholder="0" value="0">
          </div>
          <button type="submit" name="add_cat" class="btn-add">
            <i class="bi bi-plus-lg"></i> เพิ่ม
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Table -->
  <div class="table-card">
    <div class="table-card-header">
      <div class="table-card-header-left">
        <i class="bi bi-grid-3x3-gap-fill"></i>
        หมวดหมู่ทั้งหมด
        <span class="count-badge"><?= count($cats) ?></span>
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th style="width:52px">ลำดับ</th>
          <th>หมวดหมู่</th>
          <th style="width:100px">สถานะ</th>
          <th style="width:140px">จัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($cats)): ?>
          <tr>
            <td colspan="4">
              <div class="empty-state">
                <i class="bi bi-folder2-open"></i>
                <p>ยังไม่มีหมวดหมู่ กรุณาเพิ่มหมวดหมู่ใหม่</p>
              </div>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($cats as $c): ?>
            <?php $isOn = ($c['is_active'] ?? 1) == 1; ?>
            <tr>
              <!-- sort order -->
              <td><span class="sort-chip"><?= $c['sort_order'] ?></span></td>

              <!-- title -->
              <td>
                <div class="cell-title-th"><?= htmlspecialchars($c['title_th']) ?></div>
                <?php if (!empty($c['title_en'])): ?>
                  <div class="cell-title-en"><?= htmlspecialchars($c['title_en']) ?></div>
                <?php endif; ?>
              </td>

              <!-- status -->
              <td>
                <span class="status-badge <?= $isOn ? 'on' : 'off' ?>">
                  <span class="dot"></span>
                  <?= $isOn ? 'แสดง' : 'ซ่อน' ?>
                </span>
              </td>

              <!-- actions -->
              <td>
                <div class="actions">
                  <!-- จัดการรูป -->
                  <a href="showcase_images.php?id=<?= $c['id'] ?>"
                     class="act-btn images" title="จัดการรูปภาพ">
                    <i class="bi bi-images"></i>
                  </a>

                  <!-- toggle status -->
                  <a href="?toggle=<?= $c['id'] ?>"
                     class="act-btn <?= $isOn ? 'toggle-on' : 'toggle-off' ?>"
                     title="<?= $isOn ? 'ปิดการแสดง' : 'เปิดการแสดง' ?>">
                    <i class="bi <?= $isOn ? 'bi-eye-fill' : 'bi-eye-slash-fill' ?>"></i>
                  </a>

                  <!-- delete -->
                  <button class="act-btn del btn-delete"
                     data-id="<?= $c['id'] ?>"
                     data-name="<?= htmlspecialchars($c['title_th']) ?>"
                     title="ลบ">
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

</div>

<script>
  document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', () => {
      Swal.fire({
        title: 'ลบหมวดหมู่นี้?',
        html: `<span style="font-size:.9rem;color:#6b6d85">"${btn.dataset.name}"</span><br><span style="font-size:.82rem;color:#e05c5c">รูปภาพทั้งหมดในหมวดนี้จะถูกลบด้วย</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e05c5c',
        cancelButtonColor: '#4a4c62',
        confirmButtonText: 'ลบเลย',
        cancelButtonText: 'ยกเลิก',
        borderRadius: '12px'
      }).then(r => {
        if (r.isConfirmed) location.href = '?delete=' + btn.dataset.id;
      });
    });
  });
</script>

</body>
</html>