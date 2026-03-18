<?php
include '../../../config/db.php';

/* ===== SAVE ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        UPDATE about_content
        SET story=?, vision=?, mission=?
        WHERE id=1
    ");
    $stmt->execute([
        $_POST['story'],
        $_POST['vision'],
        $_POST['mission']
    ]);
    $success = true;
}

/* ===== FETCH ===== */
$data = $pdo->query("SELECT * FROM about_content WHERE id=1")
    ->fetch(PDO::FETCH_ASSOC);

require_once '../../../includes/header_db.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>จัดการเนื้อหาเกี่ยวกับเรา – PBLR Backoffice</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="/PBLR/assets/css/backend/styleaboutcm.css" rel="stylesheet">
</head>
<body>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
  <div class="loading-spinner"></div>
</div>

<div class="main-container">

  <!-- Page Header -->
  <div class="page-header">
    <h3><i class="fas fa-pen-nib"></i> จัดการเนื้อหาเกี่ยวกับเรา</h3>
    <p>แก้ไขเนื้อหา Story · วิสัยทัศน์ · พันธกิจ ที่แสดงบนหน้าเว็บไซต์</p>
  </div>

  <!-- Success Alert -->
  <?php if (!empty($success)): ?>
    <div class="alert-success">
      <i class="fas fa-check-circle"></i> บันทึกข้อมูลเรียบร้อยแล้ว
    </div>
  <?php endif; ?>

  <!-- Form -->
  <form method="post" id="aboutForm">

    <!-- Story -->
    <div class="content-card">
      <div class="content-card-header">
        <i class="fas fa-book-open"></i>
        <h5 class="title">กว่าจะมาเป็น — Our Story</h5>
      </div>
      <div class="content-card-body">
        <div class="editor-wrapper">
          <textarea id="story" name="story"><?= htmlspecialchars($data['story'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- Vision -->
    <div class="content-card">
      <div class="content-card-header">
        <i class="fas fa-eye"></i>
        <h5 class="title">วิสัยทัศน์ — Vision</h5>
      </div>
      <div class="content-card-body">
        <div class="editor-wrapper">
          <textarea id="vision" name="vision"><?= htmlspecialchars($data['vision'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- Mission -->
    <div class="content-card">
      <div class="content-card-header">
        <i class="fas fa-bullseye"></i>
        <h5 class="title">พันธกิจ — Mission</h5>
      </div>
      <div class="content-card-body">
        <div class="editor-wrapper">
          <textarea id="mission" name="mission"><?= htmlspecialchars($data['mission'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="action-buttons">
      <button type="button" class="btn-custom btn-preview" onclick="openPreview()">
        <i class="fas fa-eye"></i> ดูตัวอย่าง
      </button>
      <button type="submit" class="btn-custom btn-save">
        <i class="fas fa-save"></i> บันทึกทั้งหมด
      </button>
    </div>

  </form>
</div>


<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-eye"></i> ตัวอย่างเนื้อหา
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="preview-section">
          <h4><i class="fas fa-book-open"></i> กว่าจะมาเป็น</h4>
          <div id="pv_story"></div>
        </div>
        <div class="preview-section">
          <h4><i class="fas fa-eye"></i> วิสัยทัศน์</h4>
          <div id="pv_vision"></div>
        </div>
        <div class="preview-section">
          <h4><i class="fas fa-bullseye"></i> พันธกิจ</h4>
          <div id="pv_mission"></div>
        </div>
      </div>

    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/PBLR/assets/tinymce/tinymce.min.js"></script>
<script>
  /* Loading overlay on submit */
  document.getElementById('aboutForm').addEventListener('submit', () => {
    document.getElementById('loadingOverlay').style.display = 'flex';
  });

  /* TinyMCE */
  tinymce.init({
    selector: '#story, #vision, #mission',
    height: 340,
    license_key: 'gpl',
    plugins: 'lists link table code',
    toolbar: 'undo redo | bold italic underline | forecolor backcolor | bullist numlist | link | code',
    menubar: false,
    statusbar: false,
    skin: 'oxide-dark',
    content_css: 'dark',
    valid_elements: `p,br,strong,b,em,i,u,ul,ol,li,a[href|target],span[style],table,tr,td,th,h1,h2,h3,h4,h5,h6`,
    invalid_elements: 'script,iframe,object,embed',
    content_style: `
      body {
        background: #141628;
        color: #e2e4f0;
        font-family: 'Noto Sans Thai', 'Segoe UI', sans-serif;
        font-size: 14px;
        padding: 1rem 1.25rem;
        line-height: 1.8;
      }
    `
  });

  /* Preview modal */
  const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));

  function openPreview() {
    document.getElementById('pv_story').innerHTML   = tinymce.get('story').getContent();
    document.getElementById('pv_vision').innerHTML  = tinymce.get('vision').getContent();
    document.getElementById('pv_mission').innerHTML = tinymce.get('mission').getContent();
    previewModal.show();
  }
</script>
</body>
</html>