<?php
include '../../../config/db.php';
$id = $_GET['id'] ?? null;

/* ====================== SAVE / UPDATE ====================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id       = $_POST['id'] ?? null;
  $title_th = $_POST['title_th'];
  $title_en = $_POST['title_en'];
  $desc_th  = $_POST['description_th'];
  $desc_en  = $_POST['description_en'];
  $image    = $_POST['old_image'] ?? '';

  if (!empty($_FILES['image']['name'])) {
    $ext   = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $image = time() . '.' . $ext;
    move_uploaded_file($_FILES['image']['tmp_name'],
      "../../../assets/upload/activities/" . $image);
  }

  if ($id) {
    $pdo->prepare("UPDATE activities SET title_th=?,title_en=?,description_th=?,description_en=?,image_path=? WHERE id=?")
        ->execute([$title_th, $title_en, $desc_th, $desc_en, $image, $id]);
  } else {
    $pdo->prepare("INSERT INTO activities (title_th,title_en,description_th,description_en,image_path) VALUES (?,?,?,?,?)")
        ->execute([$title_th, $title_en, $desc_th, $desc_en, $image]);
  }

  header("Location: activity_index.php");
  exit;
}

/* ====================== LOAD DATA ====================== */
$row = ['title_th'=>'','title_en'=>'','description_th'=>'','description_en'=>'','image_path'=>''];
if ($id) {
  $stmt = $pdo->prepare("SELECT * FROM activities WHERE id=?");
  $stmt->execute([$id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
}

require_once '../../../includes/header_db.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $id ? 'แก้ไขกิจกรรม' : 'เพิ่มกิจกรรม' ?> – PBLR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Noto+Sans+Thai:wght@300;400;500;600&display=swap');

    :root {
      --sb-width:     260px;   /* must match sidebar.css */
      --night:        #0d0f1a;
      --white:        #ffffff;
      --surface:      #f0f2f5;
      --gold:         #c8a94a;
      --gold-pale:    #e6d49a;
      --gold-glow:    rgba(200,169,74,0.12);
      --gold-border:  rgba(200,169,74,0.22);
      --ink:          #1a1c2e;
      --ink-mid:      #4a4c62;
      --ink-soft:     rgba(26,28,46,0.42);
      --ink-ghost:    rgba(26,28,46,0.06);
      --danger:       #e05c5c;
      --success:      #3db88a;
      --radius:       13px;
      --radius-sm:    8px;
      --shadow-sm:    0 2px 12px rgba(13,15,26,0.07);
      --shadow-md:    0 8px 32px rgba(13,15,26,0.12);
      --ease:         cubic-bezier(0.4,0,0.2,1);
      --dur:          0.22s;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Noto Sans Thai', sans-serif;
      background: var(--surface);
      color: var(--ink);
    }

    /* ── Offset for fixed sidebar ── */
    .main-content {
      margin-left: var(--sb-width);
      min-height: 100vh;
      padding: 2.5rem 2.5rem 5rem;
    }

    @media (max-width: 991px) {
      .main-content { margin-left: 0; padding: 1.5rem 1rem 4rem; }
    }

    /* ── Page header ── */
    .page-header {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 2rem;
      padding-bottom: 1.25rem;
      border-bottom: 1px solid var(--ink-ghost);
      flex-wrap: wrap;
    }

    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      font-size: 0.82rem;
      color: var(--ink-soft);
      text-decoration: none;
      padding: 0.45rem 0.9rem;
      border: 1px solid rgba(26,28,46,0.12);
      border-radius: var(--radius-sm);
      background: var(--white);
      transition: color var(--dur), border-color var(--dur);
    }

    .back-btn:hover { color: var(--ink); border-color: var(--ink); }

    .page-title {
      font-family: 'DM Serif Display', serif;
      font-size: 1.45rem;
      color: var(--ink);
      display: flex;
      align-items: center;
      gap: 0.55rem;
    }

    .page-title .h-icon {
      width: 32px; height: 32px;
      background: var(--gold-glow);
      border: 1px solid var(--gold-border);
      border-radius: var(--radius-sm);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.85rem;
      color: var(--gold);
    }

    /* ── Layout ── */
    .editor-layout {
      display: grid;
      grid-template-columns: 1fr 280px;
      gap: 1.5rem;
      align-items: start;
    }

    @media (max-width: 860px) {
      .editor-layout { grid-template-columns: 1fr; }
      .editor-sidebar { order: -1; }
    }

    /* ── Cards ── */
    .card {
      background: var(--white);
      border-radius: var(--radius);
      border: 1px solid rgba(26,28,46,0.07);
      box-shadow: var(--shadow-sm);
      overflow: hidden;
      margin-bottom: 1.25rem;
    }

    .card-header {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      padding: 0.95rem 1.4rem;
      background: var(--night);
      border-bottom: 1px solid rgba(200,169,74,0.12);
    }

    .card-header i {
      width: 26px; height: 26px;
      background: var(--gold-glow);
      border: 1px solid var(--gold-border);
      border-radius: 6px;
      display: flex; align-items: center; justify-content: center;
      font-size: 0.7rem;
      color: var(--gold);
      flex-shrink: 0;
    }

    .card-header span {
      font-family: 'DM Serif Display', serif;
      font-size: 0.92rem;
      color: #fff;
    }

    .card-body { padding: 1.4rem; }

    /* ── Form elements ── */
    .field { margin-bottom: 1.1rem; }
    .field:last-child { margin-bottom: 0; }

    label {
      display: block;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: var(--ink-soft);
      margin-bottom: 0.4rem;
    }

    .form-input {
      width: 100%;
      padding: 0.65rem 0.9rem;
      border: 1px solid rgba(26,28,46,0.12);
      border-radius: var(--radius-sm);
      font-family: 'Noto Sans Thai', sans-serif;
      font-size: 0.88rem;
      color: var(--ink);
      background: var(--white);
      outline: none;
      transition: border-color var(--dur), box-shadow var(--dur);
    }

    .form-input:focus {
      border-color: var(--gold);
      box-shadow: 0 0 0 3px var(--gold-glow);
    }

    .form-input.lg { font-size: 1rem; padding: 0.75rem 0.9rem; font-weight: 600; }

    .lang-tag {
      display: inline-block;
      font-size: 0.65rem;
      font-weight: 700;
      letter-spacing: 0.1em;
      padding: 0.15rem 0.5rem;
      border-radius: 4px;
      margin-left: 0.4rem;
      vertical-align: middle;
    }

    .lang-tag.th { background: rgba(61,184,138,0.12); color: var(--success); }
    .lang-tag.en { background: rgba(59,130,246,0.1);  color: #3b82f6; }

    /* CKEditor wrapper */
    .ck-wrap { border-radius: var(--radius-sm); overflow: hidden; }
    .ck-wrap .cke { border: 1px solid rgba(26,28,46,0.12) !important; border-radius: var(--radius-sm) !important; }
    .ck-wrap .cke_top { background: var(--surface) !important; border-bottom: 1px solid rgba(26,28,46,0.08) !important; }

    /* ── Sidebar cards ── */
    .editor-sidebar .card { margin-bottom: 1rem; }

    /* Publish button */
    .btn-publish {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      background: var(--night);
      color: #fff;
      font-family: 'Noto Sans Thai', sans-serif;
      font-size: 0.88rem;
      font-weight: 600;
      padding: 0.75rem 1rem;
      border-radius: var(--radius-sm);
      border: none;
      cursor: pointer;
      transition: background var(--dur), transform var(--dur), box-shadow var(--dur);
      margin-bottom: 0.6rem;
    }

    .btn-publish:hover {
      background: var(--gold);
      color: var(--night);
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(200,169,74,0.28);
    }

    .btn-back-list {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      background: transparent;
      color: var(--ink-mid);
      font-family: 'Noto Sans Thai', sans-serif;
      font-size: 0.84rem;
      font-weight: 500;
      padding: 0.65rem 1rem;
      border-radius: var(--radius-sm);
      border: 1px solid rgba(26,28,46,0.12);
      cursor: pointer;
      text-decoration: none;
      transition: background var(--dur), color var(--dur);
    }

    .btn-back-list:hover { background: var(--surface); color: var(--ink); }

    /* Image preview */
    .img-preview-wrap {
      margin-bottom: 1rem;
      border-radius: var(--radius-sm);
      overflow: hidden;
      background: var(--surface);
      border: 1px dashed rgba(26,28,46,0.15);
      min-height: 120px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .img-preview-wrap img {
      width: 100%;
      display: block;
      border-radius: var(--radius-sm);
    }

    .img-placeholder {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.4rem;
      color: var(--ink-soft);
      font-size: 0.78rem;
      padding: 1.5rem;
      text-align: center;
    }

    .img-placeholder i { font-size: 1.8rem; opacity: 0.3; }

    .file-input {
      width: 100%;
      font-family: 'Noto Sans Thai', sans-serif;
      font-size: 0.8rem;
      padding: 0.55rem 0.75rem;
      border: 1px solid rgba(26,28,46,0.12);
      border-radius: var(--radius-sm);
      background: var(--white);
      color: var(--ink);
      cursor: pointer;
      outline: none;
      transition: border-color var(--dur);
    }

    .file-input:focus { border-color: var(--gold); }

    /* Meta info */
    .meta-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 0.76rem;
      color: var(--ink-soft);
      padding: 0.5rem 0;
      border-bottom: 1px solid var(--ink-ghost);
    }
    .meta-row:last-child { border-bottom: none; }
    .meta-row strong { color: var(--ink-mid); font-weight: 500; }
  </style>
</head>
<body>

<div class="main-content">

  <!-- Page Header -->
  <div class="page-header">
    <a href="activity_index.php" class="back-btn">
      <i class="bi bi-arrow-left"></i> กลับ
    </a>
    <h1 class="page-title">
      <span class="h-icon">
        <i class="bi bi-<?= $id ? 'pencil-square' : 'plus-lg' ?>"></i>
      </span>
      <?= $id ? 'แก้ไขกิจกรรม' : 'เพิ่มกิจกรรมใหม่' ?>
    </h1>
  </div>

  <!-- Form -->
  <form method="post" enctype="multipart/form-data" id="actForm">
    <input type="hidden" name="id"        value="<?= $id ?>">
    <input type="hidden" name="old_image" value="<?= htmlspecialchars($row['image_path']) ?>">

    <div class="editor-layout">

      <!-- ═══ MAIN COLUMN ═══ -->
      <div class="editor-main">

        <!-- Titles -->
        <div class="card">
          <div class="card-header">
            <i class="bi bi-type"></i>
            <span>ชื่อกิจกรรม</span>
          </div>
          <div class="card-body">
            <div class="field">
              <label>ชื่อกิจกรรม <span class="lang-tag th">TH</span></label>
              <input name="title_th" class="form-input lg"
                     placeholder="กรอกชื่อกิจกรรมภาษาไทย"
                     value="<?= htmlspecialchars($row['title_th']) ?>">
            </div>
            <div class="field">
              <label>ชื่อกิจกรรม <span class="lang-tag en">EN</span></label>
              <input name="title_en" class="form-input"
                     placeholder="Activity title in English"
                     value="<?= htmlspecialchars($row['title_en']) ?>">
            </div>
          </div>
        </div>

        <!-- Description TH -->
        <div class="card">
          <div class="card-header">
            <i class="bi bi-file-text"></i>
            <span>รายละเอียด <span class="lang-tag th" style="font-family:sans-serif">TH</span></span>
          </div>
          <div class="card-body">
            <div class="ck-wrap">
              <textarea id="editor_th" name="description_th"><?= $row['description_th'] ?></textarea>
            </div>
          </div>
        </div>

        <!-- Description EN -->
        <div class="card">
          <div class="card-header">
            <i class="bi bi-file-text"></i>
            <span>Description <span class="lang-tag en" style="font-family:sans-serif">EN</span></span>
          </div>
          <div class="card-body">
            <div class="ck-wrap">
              <textarea id="editor_en" name="description_en"><?= $row['description_en'] ?></textarea>
            </div>
          </div>
        </div>

      </div><!-- /.editor-main -->

      <!-- ═══ SIDEBAR COLUMN ═══ -->
      <div class="editor-sidebar">

        <!-- Publish -->
        <div class="card">
          <div class="card-header">
            <i class="bi bi-send"></i>
            <span><?= $id ? 'อัปเดต' : 'เผยแพร่' ?></span>
          </div>
          <div class="card-body">
            <button type="submit" class="btn-publish">
              <i class="bi bi-<?= $id ? 'arrow-repeat' : 'check-lg' ?>"></i>
              <?= $id ? 'บันทึกการแก้ไข' : 'เผยแพร่กิจกรรม' ?>
            </button>
            <a href="activity_index.php" class="btn-back-list">
              <i class="bi bi-arrow-left"></i> ยกเลิก
            </a>

            <?php if ($id): ?>
              <div style="margin-top:1rem">
                <div class="meta-row">
                  <span>ID</span>
                  <strong>#<?= $id ?></strong>
                </div>
                <div class="meta-row">
                  <span>สร้างเมื่อ</span>
                  <strong><?= date('d M Y', strtotime($row['created_at'])) ?></strong>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Featured Image -->
        <div class="card">
          <div class="card-header">
            <i class="bi bi-image"></i>
            <span>รูปภาพหลัก</span>
          </div>
          <div class="card-body">
            <div class="img-preview-wrap" id="imgPreviewWrap">
              <?php if ($row['image_path']): ?>
                <img id="imgPreview"
                     src="../../../assets/upload/activities/<?= htmlspecialchars($row['image_path']) ?>"
                     alt="preview">
              <?php else: ?>
                <div class="img-placeholder" id="imgPlaceholder">
                  <i class="bi bi-image"></i>
                  <span>ยังไม่มีรูปภาพ</span>
                </div>
              <?php endif; ?>
            </div>
            <input type="file" name="image" id="imageFile" class="file-input" accept="image/*">
          </div>
        </div>

      </div><!-- /.editor-sidebar -->

    </div><!-- /.editor-layout -->
  </form>

</div><!-- /.main-content -->

<!-- CKEditor -->
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
  CKEDITOR.replace('editor_th', { height: 320, resize_enabled: false });
  CKEDITOR.replace('editor_en', { height: 260, resize_enabled: false });

  /* Image preview on file select */
  document.getElementById('imageFile').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
      const wrap = document.getElementById('imgPreviewWrap');
      wrap.innerHTML = `<img id="imgPreview" src="${e.target.result}" alt="preview" style="width:100%;border-radius:8px">`;
    };
    reader.readAsDataURL(file);
  });
</script>
</body>
</html>