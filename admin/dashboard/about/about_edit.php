<?php
include '../../../config/db.php';

/* ================= ACTION AJAX ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

  if ($_POST['action'] === 'toggle') {
    $id = (int)$_POST['id'];
    $pdo->query("UPDATE personnel SET status = 1 - status WHERE id=$id");
    exit;
  }

  if ($_POST['action'] === 'sort') {
    $order = json_decode(file_get_contents("php://input"), true);
    foreach ($order as $i => $row) {
      $pdo->prepare("UPDATE personnel SET sort_order=? WHERE id=?")
          ->execute([$i, $row['id']]);
    }
    exit;
  }
}

/* ================= SAVE ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {

  $id       = $_POST['id'] ?? null;
  $name     = $_POST['name'];
  $position = $_POST['position'];
  $oldImage = $_POST['old_image'] ?? null;
  $imageName = $oldImage;

  if (!empty($_FILES['image']['name'])) {
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $imageName = time() . rand(100, 999) . '.' . $ext;
    move_uploaded_file(
      $_FILES['image']['tmp_name'],
      "../../../assets/upload/personnel/" . $imageName
    );
  }

  if ($id) {
    $pdo->prepare("UPDATE personnel SET name=?, position=?, image=? WHERE id=?")
        ->execute([$name, $position, $imageName, $id]);
  } else {
    $pdo->prepare("INSERT INTO personnel (name, position, image, status, sort_order) VALUES (?,?,?,1,0)")
        ->execute([$name, $position, $imageName]);
  }

  header("Location: about_edit.php");
  exit;
}

/* ================= DELETE ================= */
if (isset($_GET['delete'])) {
  $pdo->prepare("DELETE FROM personnel WHERE id=?")->execute([$_GET['delete']]);
  header("Location: about_edit.php");
  exit;
}

/* ================= FETCH ================= */
$data = $pdo->query("SELECT * FROM personnel ORDER BY sort_order ASC, id DESC")
            ->fetchAll(PDO::FETCH_ASSOC);

require_once '../../../includes/header_db.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ทำเนียบบุคลากร – PBLR Backoffice</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
  <link href="/PBLR/assets/css/backend/styleaboutper.css" rel="stylesheet">
</head>
<body>

<div class="page-wrapper">

  <!-- Header -->
  <div class="page-header">
    <div class="page-header-left">
      <h3>ทำเนียบบุคลากร</h3>
      <p>จัดการและเรียงลำดับรายชื่อบุคลากรที่แสดงบนหน้าเว็บไซต์</p>
    </div>
    <button class="btn-add" onclick="openAdd()">
      <i class="bi bi-plus-lg"></i> เพิ่มบุคลากร
    </button>
  </div>

  <!-- Table Card -->
  <div class="table-card">
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th style="width:40px"></th>
            <th style="width:70px">รูป</th>
            <th>ชื่อ – ตำแหน่ง</th>
            <th style="width:110px">สถานะ</th>
            <th style="width:130px">จัดการ</th>
          </tr>
        </thead>
        <tbody id="sortable">
          <?php if (empty($data)): ?>
            <tr>
              <td colspan="5">
                <div class="empty-state">
                  <i class="bi bi-people"></i>
                  <p>ยังไม่มีข้อมูลบุคลากร</p>
                </div>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($data as $row): ?>
              <tr data-id="<?= $row['id'] ?>">

                <!-- drag handle -->
                <td>
                  <i class="bi bi-grip-vertical drag-handle"></i>
                </td>

                <!-- avatar -->
                <td>
                  <div class="avatar-wrap">
                    <img
                      class="avatar-img"
                      src="../../../assets/upload/personnel/<?= htmlspecialchars($row['image'] ?: 'no-image.png') ?>"
                      alt="<?= htmlspecialchars($row['name']) ?>"
                    >
                  </div>
                </td>

                <!-- name + position -->
                <td>
                  <div class="cell-name"><?= htmlspecialchars($row['name']) ?></div>
                  <div class="cell-pos"><?= htmlspecialchars($row['position']) ?></div>
                </td>

                <!-- status -->
                <td>
                  <span class="status-badge <?= $row['status'] ? 'on' : 'off' ?>">
                    <span class="dot"></span>
                    <?= $row['status'] ? 'แสดง' : 'ซ่อน' ?>
                  </span>
                </td>

                <!-- actions -->
                <td>
                  <div class="actions">
                    <button class="action-btn edit" title="แก้ไข"
                      onclick='openEdit(<?= json_encode($row) ?>)'>
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="action-btn toggle <?= $row['status'] ? '' : 'off' ?>"
                      title="<?= $row['status'] ? 'ซ่อน' : 'แสดง' ?>"
                      data-id="<?= $row['id'] ?>">
                      <i class="bi <?= $row['status'] ? 'bi-eye-fill' : 'bi-eye-slash-fill' ?>"></i>
                    </button>
                    <button class="action-btn delete" title="ลบ"
                      data-id="<?= $row['id'] ?>">
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

</div><!-- /.page-wrapper -->


<!-- ══ MODAL ══ -->
<div class="modal fade" id="personnelModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form method="post" enctype="multipart/form-data" class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">เพิ่มบุคลากร</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id"        id="personnel_id">
        <input type="hidden" name="old_image" id="old_image">

        <div class="mb-3">
          <label>ชื่อ – นามสกุล</label>
          <input type="text" name="name" id="name" class="form-control" required
                 placeholder="เช่น ดร.สมชาย ใจดี">
        </div>

        <div class="mb-3">
          <label>ตำแหน่ง</label>
          <input type="text" name="position" id="position" class="form-control"
                 placeholder="เช่น นักวิจัยอาวุโส">
        </div>

        <div class="mb-2">
          <label>รูปภาพโปรไฟล์</label>
          <input type="file" name="image" id="imageInput" class="form-control"
                 accept="image/*">
        </div>

        <div class="preview-wrap">
          <img id="imagePreview" class="d-none" alt="preview">
          <span id="previewHint" style="font-size:0.78rem;color:var(--ink-soft);font-weight:300">
            รูปจะแสดงที่นี่หลังเลือกไฟล์
          </span>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn-save">
          <i class="bi bi-check-lg me-1"></i> บันทึก
        </button>
        <button type="button" class="btn-cancel" data-bs-dismiss="modal">ยกเลิก</button>
      </div>

    </form>
  </div>
</div>


<script>
const modal = new bootstrap.Modal(document.getElementById('personnelModal'));

/* image preview */
document.getElementById('imageInput').onchange = e => {
  const reader = new FileReader();
  reader.onload = ev => {
    const img = document.getElementById('imagePreview');
    img.src = ev.target.result;
    img.classList.remove('d-none');
    document.getElementById('previewHint').style.display = 'none';
  };
  reader.readAsDataURL(e.target.files[0]);
};

function openAdd() {
  document.getElementById('modalTitle').innerText = 'เพิ่มบุคลากร';
  document.getElementById('personnel_id').value = '';
  document.getElementById('old_image').value = '';
  document.getElementById('name').value = '';
  document.getElementById('position').value = '';
  const img = document.getElementById('imagePreview');
  img.classList.add('d-none');
  document.getElementById('previewHint').style.display = '';
  modal.show();
}

function openEdit(d) {
  document.getElementById('modalTitle').innerText = 'แก้ไขบุคลากร';
  document.getElementById('personnel_id').value = d.id;
  document.getElementById('name').value = d.name;
  document.getElementById('position').value = d.position;
  document.getElementById('old_image').value = d.image;
  const img = document.getElementById('imagePreview');
  if (d.image) {
    img.src = '../../../assets/upload/personnel/' + d.image;
    img.classList.remove('d-none');
    document.getElementById('previewHint').style.display = 'none';
  } else {
    img.classList.add('d-none');
    document.getElementById('previewHint').style.display = '';
  }
  modal.show();
}

/* delete */
document.querySelectorAll('.delete').forEach(btn => {
  btn.onclick = () => {
    Swal.fire({
      title: 'ลบบุคลากรนี้?',
      text: 'ไม่สามารถกู้คืนได้',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#e05c5c',
      cancelButtonColor: '#6b6d85',
      confirmButtonText: 'ลบเลย',
      cancelButtonText: 'ยกเลิก'
    }).then(r => {
      if (r.isConfirmed) location.href = '?delete=' + btn.dataset.id;
    });
  };
});

/* toggle */
document.querySelectorAll('.toggle').forEach(btn => {
  btn.onclick = () => {
    fetch('', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'action=toggle&id=' + btn.dataset.id
    }).then(() => location.reload());
  };
});

/* sortable drag-and-drop */
new Sortable(document.getElementById('sortable'), {
  animation: 200,
  handle: '.drag-handle',
  ghostClass: 'sortable-ghost',
  dragClass: 'sortable-drag',
  onEnd: () => {
    const data = [...document.querySelectorAll('#sortable tr[data-id]')]
      .map(tr => ({ id: tr.dataset.id }));
    fetch('', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
  }
});
</script>

</body>
</html>