<?php
include '../../../config/db.php';

$uploadDir = "../../../assets/upload/flags/";

/* ── AJAX ── */
if (isset($_POST['ajax'])) {
  $action = $_POST['action'];
  $icon   = $_POST['old_icon'] ?? '';

  if (!empty($_FILES['icon']['name'])) {
    $name = time() . '_' . $_FILES['icon']['name'];
    move_uploaded_file($_FILES['icon']['tmp_name'], $uploadDir . $name);
    $icon = $name;
  }

  if ($action === 'add') {
    $stmt = $pdo->prepare("INSERT INTO country_network (country_name_th,country_name_en,flag_url,network_type,status,sort_order) VALUES (?,?,?,?,1,?)");
    $stmt->execute([$_POST['name_th'], $_POST['name_en'], $icon, $_POST['network_type'], $_POST['sort_order']]);
  }

  if ($action === 'edit') {
    $stmt = $pdo->prepare("UPDATE country_network SET country_name_th=?,country_name_en=?,flag_url=?,network_type=?,sort_order=? WHERE id=?");
    $stmt->execute([$_POST['name_th'], $_POST['name_en'], $icon, $_POST['network_type'], $_POST['sort_order'], $_POST['id']]);
  }

  if ($action === 'delete') {
    $pdo->prepare("DELETE FROM country_network WHERE id=?")->execute([(int)$_POST['id']]);
  }

  if ($action === 'status') {
    $pdo->prepare("UPDATE country_network SET status=? WHERE id=?")->execute([(int)$_POST['status'], (int)$_POST['id']]);
  }

  if ($action === 'sort') {
    foreach ($_POST['order'] as $i => $id) {
      $pdo->prepare("UPDATE country_network SET sort_order=? WHERE id=?")->execute([$i, (int)$id]);
    }
  }

  echo json_encode(['success' => true]);
  exit;
}

/* ── Load ── */
$data = $pdo->query("SELECT * FROM country_network ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
include '../../../includes/header_db.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Network Countries – PBLR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Noto+Sans+Thai:wght@300;400;500;600&display=swap');

    :root {
      --sb-width:    260px;
      --night:       #0d0f1a;
      --white:       #ffffff;
      --surface:     #f0f2f5;
      --gold:        #c8a94a;
      --gold-pale:   #e6d49a;
      --gold-glow:   rgba(200,169,74,0.12);
      --gold-border: rgba(200,169,74,0.22);
      --ink:         #1a1c2e;
      --ink-mid:     #4a4c62;
      --ink-soft:    rgba(26,28,46,0.42);
      --ink-ghost:   rgba(26,28,46,0.06);
      --danger:      #e05c5c;
      --danger-bg:   rgba(224,92,92,0.08);
      --success:     #3db88a;
      --success-bg:  rgba(61,184,138,0.09);
      --info:        #3b82f6;
      --radius:      13px;
      --radius-sm:   8px;
      --shadow-sm:   0 2px 12px rgba(13,15,26,0.07);
      --shadow-md:   0 8px 32px rgba(13,15,26,0.12);
      --ease:        cubic-bezier(0.4,0,0.2,1);
      --dur:         0.22s;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Noto Sans Thai', sans-serif; background: var(--surface); color: var(--ink); }

    .main-content {
      margin-left: var(--sb-width);
      min-height: 100vh;
      padding: 2.5rem 2.5rem 5rem;
    }
    @media (max-width: 991px) { .main-content { margin-left: 0; padding: 1.5rem 1rem 4rem; } }

    /* ── Page header ── */
    .page-header {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 2rem;
      padding-bottom: 1.4rem;
      border-bottom: 1px solid var(--ink-ghost);
      flex-wrap: wrap;
    }

    .page-title {
      font-family: 'DM Serif Display', serif;
      font-size: 1.5rem;
      color: var(--ink);
      display: flex;
      align-items: center;
      gap: 0.6rem;
    }

    .h-icon {
      width: 34px; height: 34px;
      background: var(--gold-glow);
      border: 1px solid var(--gold-border);
      border-radius: var(--radius-sm);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.88rem;
      color: var(--gold);
    }

    .page-sub {
      font-size: 0.8rem;
      color: var(--ink-soft);
      font-weight: 300;
      margin-top: 0.2rem;
      padding-left: calc(34px + 0.6rem);
    }

    /* ── Stats chips ── */
    .stats-row {
      display: flex;
      gap: 0.9rem;
      margin-bottom: 1.4rem;
      flex-wrap: wrap;
    }

    .stat-chip {
      background: var(--white);
      border: 1px solid var(--ink-ghost);
      border-radius: var(--radius-sm);
      padding: 0.6rem 1.1rem;
      font-size: 0.8rem;
      color: var(--ink-soft);
      display: flex;
      align-items: center;
      gap: 0.45rem;
      box-shadow: var(--shadow-sm);
    }

    .stat-chip strong { font-size: 1rem; color: var(--ink); font-family: 'DM Serif Display', serif; font-weight: 400; }
    .stat-chip i { color: var(--gold); }

    /* ── Add button ── */
    .btn-add {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      background: var(--night);
      color: #fff;
      font-family: 'Noto Sans Thai', sans-serif;
      font-size: 0.84rem;
      font-weight: 600;
      padding: 0.6rem 1.3rem;
      border-radius: var(--radius-sm);
      border: none;
      cursor: pointer;
      transition: background var(--dur), transform var(--dur), box-shadow var(--dur);
      white-space: nowrap;
    }

    .btn-add:hover {
      background: var(--gold);
      color: var(--night);
      transform: translateY(-1px);
      box-shadow: 0 6px 18px rgba(200,169,74,0.28);
    }

    /* ── Toolbar ── */
    .toolbar {
      display: flex;
      gap: 0.75rem;
      margin-bottom: 1.1rem;
      flex-wrap: wrap;
    }

    .search-wrap {
      position: relative;
      flex: 1;
      min-width: 200px;
    }

    .search-wrap > i {
      position: absolute;
      left: 0.8rem;
      top: 50%; transform: translateY(-50%);
      color: var(--ink-soft);
      font-size: 0.8rem;
      pointer-events: none;
    }

    .search-input {
      width: 100%;
      padding: 0.6rem 0.85rem 0.6rem 2.1rem;
      border: 1px solid rgba(26,28,46,0.12);
      border-radius: var(--radius-sm);
      font-family: 'Noto Sans Thai', sans-serif;
      font-size: 0.84rem;
      color: var(--ink);
      background: var(--white);
      outline: none;
      transition: border-color var(--dur), box-shadow var(--dur);
    }

    .search-input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px var(--gold-glow); }

    .filter-select {
      padding: 0.6rem 1rem;
      border: 1px solid rgba(26,28,46,0.12);
      border-radius: var(--radius-sm);
      font-family: 'Noto Sans Thai', sans-serif;
      font-size: 0.84rem;
      color: var(--ink);
      background: var(--white);
      outline: none;
      cursor: pointer;
      transition: border-color var(--dur);
    }

    .filter-select:focus { border-color: var(--gold); }

    /* ── Table card ── */
    .table-card {
      background: var(--white);
      border-radius: var(--radius);
      border: 1px solid rgba(26,28,46,0.07);
      box-shadow: var(--shadow-sm);
      overflow: hidden;
    }

    .table-responsive { overflow-x: auto; }

    table { width: 100%; border-collapse: collapse; font-family: 'Noto Sans Thai', sans-serif; }

    thead th {
      background: var(--night);
      font-size: 0.68rem;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: rgba(255,255,255,0.45);
      padding: 0.9rem 1.1rem;
      border-bottom: 1px solid rgba(200,169,74,0.1);
      white-space: nowrap;
    }

    thead th:first-child { color: rgba(255,255,255,0.2); }

    tbody tr {
      border-bottom: 1px solid rgba(26,28,46,0.045);
      transition: background var(--dur);
    }

    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #fdfcfa; }

    tbody tr.sortable-ghost { opacity: 0.4; background: var(--gold-glow); }
    tbody tr.sortable-drag  { box-shadow: var(--shadow-md); }

    td { padding: 0.8rem 1.1rem; font-size: 0.86rem; color: var(--ink-mid); vertical-align: middle; }

    /* drag handle */
    .drag-handle {
      color: rgba(26,28,46,0.2);
      cursor: grab;
      font-size: 1rem;
      padding: 0 0.25rem;
      transition: color var(--dur);
    }

    tbody tr:hover .drag-handle { color: var(--gold); }
    .drag-handle:active { cursor: grabbing; }

    /* flag */
    .flag-img {
      width: 38px; height: 26px;
      object-fit: cover;
      border-radius: 4px;
      border: 1px solid rgba(26,28,46,0.1);
      display: block;
    }

    .flag-placeholder {
      width: 38px; height: 26px;
      background: var(--surface);
      border-radius: 4px;
      border: 1px dashed rgba(26,28,46,0.15);
      display: flex; align-items: center; justify-content: center;
      font-size: 0.65rem;
      color: var(--ink-soft);
    }

    /* country name */
    .cell-name-th { font-weight: 600; color: var(--ink); font-size: 0.88rem; }
    .cell-name-en { font-size: 0.75rem; color: var(--ink-soft); font-weight: 300; margin-top: 2px; }

    /* type badge */
    .type-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
      font-size: 0.7rem;
      font-weight: 700;
      padding: 0.22rem 0.65rem;
      border-radius: 100px;
    }

    .type-th  { background: rgba(200,169,74,0.1); color: var(--gold); border: 1px solid var(--gold-border); }
    .type-int { background: rgba(59,130,246,0.09); color: var(--info); border: 1px solid rgba(59,130,246,0.2); }

    /* status toggle */
    .status-toggle {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      font-size: 0.72rem;
      font-weight: 700;
      padding: 0.28rem 0.8rem;
      border-radius: 100px;
      border: none;
      cursor: pointer;
      transition: background var(--dur), color var(--dur), transform var(--dur);
    }

    .status-toggle:hover { transform: scale(1.04); }

    .status-toggle.on  { background: var(--success-bg); color: var(--success); border: 1px solid rgba(61,184,138,0.2); }
    .status-toggle.off { background: var(--ink-ghost);  color: var(--ink-soft); border: 1px solid rgba(26,28,46,0.1); }

    .status-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

    /* action buttons */
    .actions { display: flex; gap: 0.35rem; align-items: center; }

    .action-btn {
      width: 32px; height: 32px;
      border-radius: var(--radius-sm);
      border: 1px solid transparent;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 0.82rem;
      background: transparent;
      transition: background var(--dur), color var(--dur), border-color var(--dur), transform var(--dur);
    }

    .action-btn:hover { transform: translateY(-1px); }

    .action-btn.edit   { background: rgba(200,169,74,0.1);  color: var(--gold);    border-color: var(--gold-border); }
    .action-btn.edit:hover   { background: var(--gold);   color: var(--night); border-color: var(--gold); }

    .action-btn.delete { background: var(--danger-bg); color: var(--danger); border-color: rgba(224,92,92,0.2); }
    .action-btn.delete:hover { background: var(--danger); color: #fff; border-color: var(--danger); }

    /* table footer */
    .table-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.85rem 1.1rem;
      border-top: 1px solid var(--ink-ghost);
      font-size: 0.76rem;
      color: var(--ink-soft);
      flex-wrap: wrap;
      gap: 0.5rem;
    }

    .table-footer strong { color: var(--ink); }

    .drag-hint {
      display: flex;
      align-items: center;
      gap: 0.35rem;
      font-size: 0.72rem;
      color: rgba(26,28,46,0.28);
    }

    /* ── MODAL ── */
    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(13,15,26,0.65);
      backdrop-filter: blur(6px);
      z-index: 1000;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.25s var(--ease);
    }

    .modal-overlay.show {
      opacity: 1;
      pointer-events: all;
    }

    .modal-box {
      background: var(--white);
      border-radius: var(--radius);
      width: 100%;
      max-width: 480px;
      box-shadow: 0 32px 80px rgba(13,15,26,0.35);
      overflow: hidden;
      transform: translateY(16px) scale(0.98);
      transition: transform 0.28s var(--ease);
    }

    .modal-overlay.show .modal-box { transform: none; }

    .modal-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1.1rem 1.5rem;
      background: var(--night);
      border-bottom: 1px solid rgba(200,169,74,0.12);
    }

    .modal-head-title {
      font-family: 'DM Serif Display', serif;
      font-size: 1rem;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .modal-head-title i {
      width: 24px; height: 24px;
      background: var(--gold-glow);
      border: 1px solid var(--gold-border);
      border-radius: 5px;
      display: flex; align-items: center; justify-content: center;
      font-size: 0.65rem;
      color: var(--gold);
    }

    .modal-close {
      width: 28px; height: 28px;
      background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 6px;
      color: rgba(255,255,255,0.5);
      font-size: 0.9rem;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      transition: background var(--dur), color var(--dur);
    }

    .modal-close:hover { background: rgba(255,255,255,0.14); color: #fff; }

    .modal-body { padding: 1.5rem; }

    .form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    @media (max-width: 480px) { .form-row-2 { grid-template-columns: 1fr; } }

    .field { margin-bottom: 1.05rem; }
    .field:last-child { margin-bottom: 0; }

    .field label {
      display: block;
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 0.09em;
      text-transform: uppercase;
      color: var(--ink-soft);
      margin-bottom: 0.38rem;
    }

    .field input, .field select {
      width: 100%;
      padding: 0.65rem 0.9rem;
      border: 1px solid rgba(26,28,46,0.12);
      border-radius: var(--radius-sm);
      font-family: 'Noto Sans Thai', sans-serif;
      font-size: 0.86rem;
      color: var(--ink);
      background: var(--white);
      outline: none;
      transition: border-color var(--dur), box-shadow var(--dur);
    }

    .field input:focus, .field select:focus {
      border-color: var(--gold);
      box-shadow: 0 0 0 3px var(--gold-glow);
    }

    .field input[type="file"] { padding: 0.5rem 0.75rem; cursor: pointer; }

    /* Flag preview */
    .flag-preview-wrap {
      width: 72px; height: 50px;
      border-radius: 7px;
      overflow: hidden;
      background: var(--surface);
      border: 1.5px dashed rgba(26,28,46,0.15);
      display: flex; align-items: center; justify-content: center;
      margin-top: 0.65rem;
      transition: border-color var(--dur);
    }

    .flag-preview-wrap.has-img { border-style: solid; border-color: var(--gold-border); }
    .flag-preview-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }

    /* Modal footer */
    .modal-foot {
      display: flex;
      gap: 0.6rem;
      justify-content: flex-end;
      padding: 1.1rem 1.5rem;
      border-top: 1px solid var(--ink-ghost);
    }

    .btn-save {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      background: var(--night);
      color: #fff;
      font-family: 'Noto Sans Thai', sans-serif;
      font-size: 0.84rem;
      font-weight: 600;
      padding: 0.6rem 1.4rem;
      border-radius: var(--radius-sm);
      border: none;
      cursor: pointer;
      transition: background var(--dur), transform var(--dur);
    }

    .btn-save:hover { background: var(--gold); color: var(--night); transform: translateY(-1px); }

    .btn-cancel {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      background: transparent;
      color: var(--ink-mid);
      font-family: 'Noto Sans Thai', sans-serif;
      font-size: 0.84rem;
      padding: 0.6rem 1.2rem;
      border-radius: var(--radius-sm);
      border: 1px solid rgba(26,28,46,0.12);
      cursor: pointer;
      transition: background var(--dur);
    }

    .btn-cancel:hover { background: var(--surface); }

    /* Empty state */
    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      color: var(--ink-soft);
    }

    .empty-state i { font-size: 2.5rem; margin-bottom: 0.75rem; display: block; opacity: 0.22; }
    .empty-state p { font-size: 0.88rem; font-weight: 300; }
  </style>
</head>
<body>

<div class="main-content">

  <!-- Header -->
  <div class="page-header">
    <div>
      <h1 class="page-title">
        <span class="h-icon"><i class="bi bi-globe2"></i></span>
        Network Countries
      </h1>
      <p class="page-sub">จัดการประเทศในเครือข่าย — ลากเพื่อเรียงลำดับได้</p>
    </div>
    <button class="btn-add" onclick="openAdd()">
      <i class="bi bi-plus-lg"></i> เพิ่มประเทศ
    </button>
  </div>

  <!-- Stats -->
  <div class="stats-row">
    <div class="stat-chip">
      <i class="bi bi-globe"></i>
      <span>ทั้งหมด <strong id="totalCount"><?= count($data) ?></strong> ประเทศ</span>
    </div>
    <div class="stat-chip">
      <i class="bi bi-flag"></i>
      <span>ในประเทศ <strong><?= count(array_filter($data, fn($r) => $r['network_type'] === 'TH')) ?></strong></span>
    </div>
    <div class="stat-chip">
      <i class="bi bi-airplane"></i>
      <span>ต่างประเทศ <strong><?= count(array_filter($data, fn($r) => $r['network_type'] === 'INT')) ?></strong></span>
    </div>
  </div>

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="search-wrap">
      <i class="bi bi-search"></i>
      <input type="text" class="search-input" id="search" placeholder="ค้นหาชื่อประเทศ...">
    </div>
    <select class="filter-select" id="filterType">
      <option value="">ทุกประเภท</option>
      <option value="TH">ในประเทศ</option>
      <option value="INT">ต่างประเทศ</option>
    </select>
  </div>

  <!-- Table -->
  <div class="table-card">
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th style="width:44px"></th>
            <th style="width:68px">ธง</th>
            <th>ชื่อประเทศ</th>
            <th style="width:120px">ประเภท</th>
            <th style="width:100px">สถานะ</th>
            <th style="width:110px; text-align:center">จัดการ</th>
          </tr>
        </thead>
        <tbody id="tbody">
          <?php if (empty($data)): ?>
            <tr id="emptyRow">
              <td colspan="6">
                <div class="empty-state">
                  <i class="bi bi-globe2"></i>
                  <p>ยังไม่มีข้อมูลประเทศ</p>
                </div>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($data as $r): ?>
              <tr data-id="<?= $r['id'] ?>" data-type="<?= $r['network_type'] ?>">
                <td>
                  <span class="drag-handle bi bi-grip-vertical"></span>
                </td>
                <td>
                  <?php if ($r['flag_url']): ?>
                    <img class="flag-img"
                         src="/PBLR/assets/upload/flags/<?= htmlspecialchars($r['flag_url']) ?>"
                         alt="<?= htmlspecialchars($r['country_name_en']) ?>">
                  <?php else: ?>
                    <div class="flag-placeholder"><i class="bi bi-image"></i></div>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="cell-name-th"><?= htmlspecialchars($r['country_name_th']) ?></div>
                  <div class="cell-name-en"><?= htmlspecialchars($r['country_name_en']) ?></div>
                </td>
                <td>
                  <span class="type-badge <?= $r['network_type'] === 'TH' ? 'type-th' : 'type-int' ?>">
                    <i class="bi bi-<?= $r['network_type'] === 'TH' ? 'house' : 'airplane' ?>"></i>
                    <?= $r['network_type'] === 'TH' ? 'ในประเทศ' : 'ต่างประเทศ' ?>
                  </span>
                </td>
                <td>
                  <button class="status-toggle <?= $r['status'] ? 'on' : 'off' ?>"
                          onclick="toggleStatus(<?= $r['id'] ?>, <?= $r['status'] ? 0 : 1 ?>, this)">
                    <span class="status-dot"></span>
                    <?= $r['status'] ? 'เปิดใช้' : 'ปิดอยู่' ?>
                  </button>
                </td>
                <td>
                  <div class="actions">
                    <button class="action-btn edit" title="แก้ไข"
                            onclick='openEdit(<?= json_encode($r) ?>)'>
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="action-btn delete" title="ลบ"
                            onclick="del(<?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['country_name_th'])) ?>')">
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
      <span>แสดง <strong id="visibleCount"><?= count($data) ?></strong> ประเทศ</span>
      <span class="drag-hint">
        <i class="bi bi-grip-vertical"></i> ลากแถวเพื่อเรียงลำดับ
      </span>
    </div>
  </div>

</div>

<!-- ══ MODAL ══ -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal-box">

    <div class="modal-head">
      <div class="modal-head-title">
        <i class="bi bi-globe2" id="modalIcon"></i>
        <span id="modalTitle">เพิ่มประเทศ</span>
      </div>
      <button class="modal-close" onclick="closeModal()">
        <i class="bi bi-x"></i>
      </button>
    </div>

    <div class="modal-body">
      <input type="hidden" id="mId">
      <input type="hidden" id="mOldIcon">

      <div class="form-row-2">
        <div class="field">
          <label>ชื่อไทย</label>
          <input id="mNameTh" placeholder="เช่น ประเทศไทย">
        </div>
        <div class="field">
          <label>ชื่ออังกฤษ</label>
          <input id="mNameEn" placeholder="e.g. Thailand">
        </div>
      </div>

      <div class="form-row-2">
        <div class="field">
          <label>ประเภท</label>
          <select id="mType">
            <option value="TH">ในประเทศ</option>
            <option value="INT">ต่างประเทศ</option>
          </select>
        </div>
        <div class="field">
          <label>ลำดับ (Sort)</label>
          <input type="number" id="mSort" value="0" min="0">
        </div>
      </div>

      <div class="field">
        <label>รูปธง</label>
        <input type="file" id="mIcon" accept="image/*">
        <div class="flag-preview-wrap" id="flagPreviewWrap">
          <img id="flagPreview" src="" alt="" style="display:none">
          <i class="bi bi-image" id="flagPlaceholderIcon" style="color:rgba(26,28,46,0.2);font-size:1.2rem"></i>
        </div>
      </div>
    </div>

    <div class="modal-foot">
      <button class="btn-cancel" onclick="closeModal()">
        <i class="bi bi-x"></i> ยกเลิก
      </button>
      <button class="btn-save" onclick="save()">
        <i class="bi bi-check-lg"></i> บันทึก
      </button>
    </div>
  </div>
</div>

<script>
  let currentAction = 'add';

  /* ── Flag preview in modal ── */
  document.getElementById('mIcon').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
      const img  = document.getElementById('flagPreview');
      const wrap = document.getElementById('flagPreviewWrap');
      const ph   = document.getElementById('flagPlaceholderIcon');
      img.src = e.target.result;
      img.style.display = 'block';
      ph.style.display  = 'none';
      wrap.classList.add('has-img');
    };
    reader.readAsDataURL(file);
  });

  /* ── Open add ── */
  function openAdd() {
    currentAction = 'add';
    document.getElementById('modalTitle').textContent = 'เพิ่มประเทศใหม่';
    document.getElementById('mId').value      = '';
    document.getElementById('mOldIcon').value = '';
    document.getElementById('mNameTh').value  = '';
    document.getElementById('mNameEn').value  = '';
    document.getElementById('mSort').value    = 0;
    document.getElementById('mType').value    = 'TH';
    resetFlagPreview();
    document.getElementById('modalOverlay').classList.add('show');
  }

  /* ── Open edit ── */
  function openEdit(d) {
    currentAction = 'edit';
    document.getElementById('modalTitle').textContent = 'แก้ไขประเทศ';
    document.getElementById('mId').value      = d.id;
    document.getElementById('mOldIcon').value = d.flag_url;
    document.getElementById('mNameTh').value  = d.country_name_th;
    document.getElementById('mNameEn').value  = d.country_name_en;
    document.getElementById('mSort').value    = d.sort_order;
    document.getElementById('mType').value    = d.network_type;

    const img  = document.getElementById('flagPreview');
    const wrap = document.getElementById('flagPreviewWrap');
    const ph   = document.getElementById('flagPlaceholderIcon');

    if (d.flag_url) {
      img.src = '/PBLR/assets/upload/flags/' + d.flag_url;
      img.style.display = 'block';
      ph.style.display  = 'none';
      wrap.classList.add('has-img');
    } else {
      resetFlagPreview();
    }
    document.getElementById('modalOverlay').classList.add('show');
  }

  function resetFlagPreview() {
    const img  = document.getElementById('flagPreview');
    const wrap = document.getElementById('flagPreviewWrap');
    const ph   = document.getElementById('flagPlaceholderIcon');
    img.src = ''; img.style.display = 'none';
    ph.style.display = 'block';
    wrap.classList.remove('has-img');
    document.getElementById('mIcon').value = '';
  }

  function closeModal() {
    document.getElementById('modalOverlay').classList.remove('show');
  }

  /* Close on backdrop click */
  document.getElementById('modalOverlay').addEventListener('click', function (e) {
    if (e.target === this) closeModal();
  });

  /* ── Save ── */
  function save() {
    const fd = new FormData();
    fd.append('ajax',         1);
    fd.append('action',       currentAction);
    fd.append('id',           document.getElementById('mId').value);
    fd.append('old_icon',     document.getElementById('mOldIcon').value);
    fd.append('name_th',      document.getElementById('mNameTh').value);
    fd.append('name_en',      document.getElementById('mNameEn').value);
    fd.append('network_type', document.getElementById('mType').value);
    fd.append('sort_order',   document.getElementById('mSort').value);
    const file = document.getElementById('mIcon').files[0];
    if (file) fd.append('icon', file);

    fetch('', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(() => window.location.replace(window.location.pathname));
  }

  /* ── Delete ── */
  function del(id, name) {
    Swal.fire({
      title:   'ลบประเทศนี้?',
      html:    `<span style="font-size:.88rem;color:#6b6d85"><b>${name}</b> จะถูกลบออกจากระบบ</span>`,
      icon:    'warning',
      showCancelButton:   true,
      confirmButtonColor: '#e05c5c',
      cancelButtonColor:  '#4a4c62',
      confirmButtonText:  'ลบเลย',
      cancelButtonText:   'ยกเลิก'
    }).then(r => {
      if (!r.isConfirmed) return;
      const fd = new FormData();
      fd.append('ajax', 1); fd.append('action', 'delete'); fd.append('id', id);
      fetch('', { method: 'POST', body: fd }).then(() => window.location.replace(window.location.pathname));
    });
  }

  /* ── Toggle status ── */
  function toggleStatus(id, newStatus, btn) {
    const fd = new FormData();
    fd.append('ajax', 1); fd.append('action', 'status');
    fd.append('id', id); fd.append('status', newStatus);

    fetch('', { method: 'POST', body: fd }).then(() => {
      btn.classList.toggle('on',  newStatus === 1);
      btn.classList.toggle('off', newStatus === 0);
      btn.innerHTML = `<span class="status-dot"></span>${newStatus ? 'เปิดใช้' : 'ปิดอยู่'}`;
      btn.setAttribute('onclick', `toggleStatus(${id}, ${newStatus ? 0 : 1}, this)`);
    });
  }

  /* ── Search & filter ── */
  document.getElementById('search').addEventListener('input', filter);
  document.getElementById('filterType').addEventListener('change', filter);

  function filter() {
    const q    = document.getElementById('search').value.toLowerCase();
    const type = document.getElementById('filterType').value;
    let n = 0;
    document.querySelectorAll('#tbody tr[data-id]').forEach(tr => {
      const nameTh = tr.querySelector('.cell-name-th')?.textContent.toLowerCase() ?? '';
      const nameEn = tr.querySelector('.cell-name-en')?.textContent.toLowerCase() ?? '';
      const t      = tr.dataset.type;
      const show   = (!q || nameTh.includes(q) || nameEn.includes(q))
                  && (!type || t === type);
      tr.style.display = show ? '' : 'none';
      if (show) n++;
    });
    document.getElementById('visibleCount').textContent = n;
  }

  /* ── Drag & sort ── */
  Sortable.create(document.getElementById('tbody'), {
    handle:    '.drag-handle',
    animation: 180,
    ghostClass: 'sortable-ghost',
    dragClass:  'sortable-drag',
    onEnd() {
      const order = [...document.querySelectorAll('#tbody tr[data-id]')]
                      .map(tr => tr.dataset.id);
      const fd = new FormData();
      fd.append('ajax', 1); fd.append('action', 'sort');
      order.forEach(id => fd.append('order[]', id));
      fetch('', { method: 'POST', body: fd });
    }
  });
</script>
</body>
</html>