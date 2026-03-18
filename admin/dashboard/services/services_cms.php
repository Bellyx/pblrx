<?php
require_once __DIR__ . '/../../../config/db.php';

function createSlug($text) {
  $text = strtolower($text);
  $text = preg_replace('/[^a-z0-9]+/', '-', $text);
  return trim($text, '-');
}

/* ADD */
if (isset($_POST['add'])) {
  $slug  = createSlug($_POST['title_en']);
  $image = '';
  if (!empty($_FILES['image']['name'])) {
    $image = time() . '_' . $_FILES['image']['name'];
    move_uploaded_file($_FILES['image']['tmp_name'], "../../../assets/upload/services/" . $image);
  }
  $pdo->prepare("INSERT INTO services (title_th,title_en,slug,icon,image,video_url,link_url,is_active,sort_order) VALUES (?,?,?,?,?,?,?,1,?)")
      ->execute([$_POST['title_th'], $_POST['title_en'], $slug, $_POST['icon'], $image, $_POST['video_url'], $_POST['link_url'], $_POST['sort_order']]);
  header("Location: services_cms.php"); exit;
}

/* DELETE */
if (isset($_GET['delete'])) {
  $pdo->prepare("DELETE FROM services WHERE id=?")->execute([$_GET['delete']]);
  header("Location: services_cms.php"); exit;
}

/* TOGGLE */
if (isset($_GET['toggle'])) {
  $pdo->prepare("UPDATE services SET is_active = IF(is_active=1,0,1) WHERE id=?")->execute([$_GET['toggle']]);
  header("Location: services_cms.php"); exit;
}

$services = $pdo->query("SELECT * FROM services ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
$total    = count($services);
$active   = count(array_filter($services, fn($s) => $s['is_active']));

require_once '../../../includes/header_db.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Services CMS – PBLR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="/PBLR/assets/css/backend/stylesercms.css" rel="stylesheet">
</head>
<body>

<div class="main-content">

  <!-- Header -->
  <div class="page-header">
    <div>
      <h1 class="page-title">
        <span class="h-icon"><i class="bi bi-briefcase"></i></span>
        Services CMS
      </h1>
      <p class="page-sub">จัดการบริการที่แสดงบนหน้าเว็บไซต์</p>
    </div>
    <button class="form-toggle-btn" onclick="toggleForm(this)">
      <i class="bi bi-plus-lg" id="toggleIcon"></i>
      <span id="toggleLabel">เพิ่มบริการใหม่</span>
    </button>
  </div>

  <!-- Stats -->
  <div class="stats-row">
    <div class="stat-chip"><i class="bi bi-briefcase"></i> ทั้งหมด <strong><?= $total ?></strong> รายการ</div>
    <div class="stat-chip"><i class="bi bi-check-circle"></i> เปิดใช้งาน <strong><?= $active ?></strong> รายการ</div>
    <div class="stat-chip"><i class="bi bi-eye-slash"></i> ปิดอยู่ <strong><?= $total - $active ?></strong> รายการ</div>
  </div>

  <!-- Add Form -->
  <div class="form-collapse" id="addFormWrap">
    <div class="card">
      <div class="card-header">
        <i class="bi bi-plus-lg"></i>
        <span>เพิ่มบริการใหม่</span>
      </div>
      <div class="card-body">
        <form method="post" enctype="multipart/form-data">

          <div class="form-grid" style="margin-bottom:1rem">
            <div class="field">
              <label class="field-label">ชื่อบริการ (TH)</label>
              <input name="title_th" class="form-input" placeholder="ชื่อบริการภาษาไทย" required>
            </div>
            <div class="field">
              <label class="field-label">ชื่อบริการ (EN)</label>
              <input name="title_en" class="form-input" placeholder="Service name in English" required>
            </div>
          </div>

          <div class="form-grid" style="margin-bottom:1rem">
            <div class="field">
              <label class="field-label">Icon URL</label>
              <div class="icon-row">
                <input name="icon" id="iconInput" class="form-input" placeholder="https://...icon.svg">
                <div class="icon-preview-box" id="iconPreviewBox">
                  <img id="iconPreview" src="" alt="" style="display:none">
                </div>
              </div>
            </div>
            <div class="field">
              <label class="field-label">รูปภาพ (Cover)</label>
              <input type="file" name="image" id="imageInput" class="form-input" accept="image/*">
              <div class="img-preview-wrap" id="imgPreviewWrap">
                <div class="img-ph"><i class="bi bi-image"></i><span>เลือกรูปภาพ</span></div>
              </div>
            </div>
          </div>

          <div class="form-grid cols-3" style="margin-bottom:1rem">
            <div class="field">
              <label class="field-label">Video URL</label>
              <input name="video_url" id="videoInput" class="form-input" placeholder="https://youtube.com/embed/...">
              <iframe id="videoPreview" class="video-preview" allowfullscreen></iframe>
            </div>
            <div class="field">
              <label class="field-label">Link URL</label>
              <input name="link_url" class="form-input" placeholder="https://...">
            </div>
            <div class="field">
              <label class="field-label">Sort Order</label>
              <input type="number" name="sort_order" class="form-input" placeholder="0" value="0" min="0">
            </div>
          </div>

          <div class="form-foot">
            <button type="button" class="form-toggle-btn" style="background:transparent;color:var(--ink-mid);border:1px solid rgba(26,28,46,0.12);box-shadow:none" onclick="toggleForm(null)">
              ยกเลิก
            </button>
            <button type="submit" name="add" class="btn-submit">
              <i class="bi bi-check-lg"></i> บันทึกบริการ
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>

  <!-- Table -->
  <div class="table-card">
    <div class="tbl-toolbar">
      <div class="search-wrap">
        <i class="bi bi-search"></i>
        <input type="text" class="search-input" id="search" placeholder="ค้นหาชื่อบริการ...">
      </div>
    </div>

    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th style="width:44px">ID</th>
            <th>ชื่อบริการ</th>
            <th style="width:160px">Media</th>
            <th style="width:100px">สถานะ</th>
            <th style="width:100px;text-align:center">จัดการ</th>
          </tr>
        </thead>
        <tbody id="tbody">
          <?php if (empty($services)): ?>
            <tr>
              <td colspan="5">
                <div class="empty-state">
                  <i class="bi bi-briefcase"></i>
                  <p>ยังไม่มีบริการ กด "เพิ่มบริการใหม่" เพื่อเริ่มต้น</p>
                </div>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($services as $s): ?>
              <tr>
                <td><span class="cell-id"><?= $s['id'] ?></span></td>
                <td>
                  <div class="cell-name-th"><?= htmlspecialchars($s['title_th']) ?></div>
                  <div class="cell-name-en"><?= htmlspecialchars($s['title_en']) ?></div>
                </td>
                <td>
                  <div class="preview-group">
                    <?php if ($s['icon']): ?>
                      <img src="<?= htmlspecialchars($s['icon']) ?>" class="icon-img" title="Icon">
                    <?php endif; ?>
                    <?php if ($s['image']): ?>
                      <img src="/PBLR/assets/upload/services/<?= htmlspecialchars($s['image']) ?>" class="thumb-img" title="Cover image">
                    <?php endif; ?>
                    <?php if ($s['video_url']): ?>
                      <span class="video-chip"><i class="bi bi-play-circle"></i> Video</span>
                    <?php endif; ?>
                    <?php if (!$s['icon'] && !$s['image'] && !$s['video_url']): ?>
                      <span style="font-size:.74rem;color:var(--ink-soft);font-weight:300">—</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <a href="?toggle=<?= $s['id'] ?>" class="status-badge <?= $s['is_active'] ? 'on' : 'off' ?>">
                    <span class="status-dot"></span>
                    <?= $s['is_active'] ? 'เปิดใช้' : 'ปิดอยู่' ?>
                  </a>
                </td>
                <td>
                  <div class="actions" style="justify-content:center">
                    <a href="services_edit.php?id=<?= $s['id'] ?>" class="action-btn edit" title="แก้ไข">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <button class="action-btn delete" title="ลบ"
                            onclick="confirmDelete(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['title_th'])) ?>')">
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
      <span>แสดง <strong id="visibleCount"><?= $total ?></strong> จาก <strong><?= $total ?></strong> รายการ</span>
    </div>
  </div>

</div>

<script>
  /* Toggle add form */
  let formOpen = false;
  function toggleForm(btn) {
    formOpen = !formOpen;
    document.getElementById('addFormWrap').classList.toggle('open', formOpen);
    if (btn) {
      document.getElementById('toggleIcon').className  = formOpen ? 'bi bi-x-lg' : 'bi bi-plus-lg';
      document.getElementById('toggleLabel').textContent = formOpen ? 'ยกเลิก' : 'เพิ่มบริการใหม่';
    } else {
      formOpen = false;
      document.getElementById('addFormWrap').classList.remove('open');
      document.getElementById('toggleIcon').className  = 'bi bi-plus-lg';
      document.getElementById('toggleLabel').textContent = 'เพิ่มบริการใหม่';
    }
  }

  /* Icon URL preview */
  document.getElementById('iconInput').addEventListener('input', function () {
    const img = document.getElementById('iconPreview');
    if (this.value) { img.src = this.value; img.style.display = 'block'; }
    else img.style.display = 'none';
  });

  /* Cover image preview */
  document.getElementById('imageInput').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const wrap = document.getElementById('imgPreviewWrap');
    const reader = new FileReader();
    reader.onload = e => {
      wrap.classList.add('has-img');
      wrap.innerHTML = `<img src="${e.target.result}" alt="preview">`;
    };
    reader.readAsDataURL(file);
  });

  /* Video URL preview */
  document.getElementById('videoInput').addEventListener('input', function () {
    const frame = document.getElementById('videoPreview');
    if (this.value) { frame.src = this.value; frame.classList.add('show'); }
    else { frame.src = ''; frame.classList.remove('show'); }
  });

  /* Search filter */
  document.getElementById('search').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    let n = 0;
    document.querySelectorAll('#tbody tr[data-id], #tbody tr').forEach(tr => {
      const th = tr.querySelector('.cell-name-th')?.textContent.toLowerCase() ?? '';
      const en = tr.querySelector('.cell-name-en')?.textContent.toLowerCase() ?? '';
      const show = !q || th.includes(q) || en.includes(q);
      tr.style.display = show ? '' : 'none';
      if (show) n++;
    });
    document.getElementById('visibleCount').textContent = n;
  });

  /* Delete confirm */
  function confirmDelete(id, name) {
    Swal.fire({
      title:   'ลบบริการนี้?',
      html:    `<span style="font-size:.88rem;color:#6b6d85"><b>${name}</b> จะถูกลบออกจากระบบ</span>`,
      icon:    'warning',
      showCancelButton:   true,
      confirmButtonColor: '#e05c5c',
      cancelButtonColor:  '#4a4c62',
      confirmButtonText:  'ลบเลย',
      cancelButtonText:   'ยกเลิก'
    }).then(r => { if (r.isConfirmed) location.href = '?delete=' + id; });
  }
</script>
</body>
</html>