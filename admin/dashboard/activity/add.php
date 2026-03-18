<?php
include '../../../config/db.php';

function createSlug($text) {
  $text = strtolower($text);
  $text = preg_replace('/[^a-z0-9]+/', '-', $text);
  return trim($text, '-');
}

if ($_POST) {
  $title_th = $_POST['title_th'];
  $title_en = $_POST['title_en'];
  $desc_th  = $_POST['description_th'];
  $desc_en  = $_POST['description_en'];
  $slug     = createSlug($title_en);
  $image    = '';

  if (!empty($_FILES['image']['name'])) {
    $ext   = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $image = time() . '.' . $ext;
    move_uploaded_file($_FILES['image']['tmp_name'],
      "../../../assets/upload/activities/" . $image);
  }

  $stmt = $pdo->prepare("
    INSERT INTO activities (slug,title_th,title_en,description_th,description_en,image_path,created_at)
    VALUES (?,?,?,?,?,?,NOW())
  ");
  $stmt->execute([$slug, $title_th, $title_en, $desc_th, $desc_en, $image]);
  $activity_id = $pdo->lastInsertId();

  if (!empty($_FILES['gallery']['name'][0])) {
    foreach ($_FILES['gallery']['tmp_name'] as $key => $tmp) {
      $ext  = pathinfo($_FILES['gallery']['name'][$key], PATHINFO_EXTENSION);
      $name = time() . rand(100,999) . '.' . $ext;
      move_uploaded_file($tmp, "../../../assets/upload/activities/" . $name);
      $pdo->prepare("INSERT INTO activity_images (activity_id,image_path) VALUES (?,?)")
          ->execute([$activity_id, $name]);
    }
  }

  header("Location: activity_index.php");
  exit;
}

include '../../../includes/header_db.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>เพิ่มกิจกรรม – PBLR Backoffice</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
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
      --success:     #3db88a;
      --radius:      13px;
      --radius-sm:   8px;
      --shadow-sm:   0 2px 12px rgba(13,15,26,0.07);
      --shadow-md:   0 8px 32px rgba(13,15,26,0.12);
      --ease:        cubic-bezier(0.4,0,0.2,1);
      --dur:         0.22s;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Noto Sans Thai', sans-serif;
      background: var(--surface);
      color: var(--ink);
    }

    /* ── Sidebar offset ── */
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

    /* ── Card ── */
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
      font-size: 0.72rem;
      color: var(--gold);
      flex-shrink: 0;
    }

    .card-header span {
      font-family: 'DM Serif Display', serif;
      font-size: 0.92rem;
      color: #fff;
    }

    .card-body { padding: 1.4rem; }

    /* ── Form ── */
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

    /* Slug row */
    .slug-row {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .slug-prefix {
      font-size: 0.78rem;
      color: var(--ink-soft);
      white-space: nowrap;
      background: var(--surface);
      border: 1px solid rgba(26,28,46,0.12);
      border-right: none;
      border-radius: var(--radius-sm) 0 0 var(--radius-sm);
      padding: 0.65rem 0.75rem;
      font-family: 'DM Mono', monospace;
    }

    #slug {
      border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
      background: var(--surface);
      color: var(--ink-soft);
      font-family: 'DM Mono', monospace;
      font-size: 0.82rem;
      flex: 1;
    }

    .lang-tag {
      display: inline-block;
      font-size: 0.65rem;
      font-weight: 700;
      letter-spacing: 0.1em;
      padding: 0.15rem 0.5rem;
      border-radius: 4px;
      margin-left: 0.4rem;
    }

    .lang-tag.th { background: rgba(61,184,138,0.12); color: var(--success); }
    .lang-tag.en { background: rgba(59,130,246,0.1);  color: #3b82f6; }

    /* CKEditor */
    .ck-wrap .cke { border: 1px solid rgba(26,28,46,0.12) !important; border-radius: var(--radius-sm) !important; }
    .ck-wrap .cke_top { background: var(--surface) !important; border-bottom: 1px solid rgba(26,28,46,0.08) !important; }

    /* ── Image Preview ── */
    .img-preview-wrap {
      margin-bottom: 1rem;
      border-radius: var(--radius-sm);
      overflow: hidden;
      background: var(--surface);
      border: 1.5px dashed rgba(26,28,46,0.15);
      min-height: 110px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: border-color var(--dur);
    }

    .img-preview-wrap.has-image { border-style: solid; border-color: var(--gold-border); }

    #coverPreview { width: 100%; display: block; border-radius: var(--radius-sm); }

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

    .img-placeholder i { font-size: 1.8rem; opacity: 0.25; }

    /* Gallery preview */
    .gallery-preview {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 0.5rem;
      margin-top: 0.75rem;
    }

    .gallery-preview img {
      width: 100%; aspect-ratio: 1;
      object-fit: cover;
      border-radius: 6px;
      border: 1px solid var(--ink-ghost);
    }

    .gallery-count {
      font-size: 0.75rem;
      color: var(--ink-soft);
      margin-top: 0.4rem;
      font-weight: 300;
    }

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

    /* ── Sidebar buttons ── */
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

    .btn-cancel {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      background: transparent;
      color: var(--ink-mid);
      font-family: 'Noto Sans Thai', sans-serif;
      font-size: 0.84rem;
      padding: 0.65rem 1rem;
      border-radius: var(--radius-sm);
      border: 1px solid rgba(26,28,46,0.12);
      cursor: pointer;
      text-decoration: none;
      transition: background var(--dur), color var(--dur);
    }

    .btn-cancel:hover { background: var(--surface); color: var(--ink); }

    /* Tips box */
    .tip-box {
      background: var(--gold-glow);
      border: 1px solid var(--gold-border);
      border-radius: var(--radius-sm);
      padding: 0.9rem 1rem;
      font-size: 0.78rem;
      color: var(--ink-mid);
      line-height: 1.6;
      margin-top: 0.5rem;
    }

    .tip-box i { color: var(--gold); margin-right: 0.3rem; }
  </style>
</head>
<body>

<div class="main-content">

  <!-- Header -->
  <div class="page-header">
    <a href="activity_index.php" class="back-btn">
      <i class="bi bi-arrow-left"></i> กลับ
    </a>
    <h1 class="page-title">
      <span class="h-icon"><i class="bi bi-plus-lg"></i></span>
      เพิ่มกิจกรรมใหม่
    </h1>
  </div>

  <!-- Form -->
  <form method="post" enctype="multipart/form-data" id="addForm">
    <div class="editor-layout">

      <!-- ═══ MAIN COLUMN ═══ -->
      <div class="editor-main">

        <!-- Titles & Slug -->
        <div class="card">
          <div class="card-header">
            <i class="bi bi-type"></i>
            <span>ชื่อกิจกรรม &amp; Slug</span>
          </div>
          <div class="card-body">
            <div class="field">
              <label>ชื่อกิจกรรม <span class="lang-tag th">TH</span></label>
              <input name="title_th" class="form-input lg" required
                     placeholder="กรอกชื่อกิจกรรมภาษาไทย">
            </div>
            <div class="field">
              <label>ชื่อกิจกรรม <span class="lang-tag en">EN</span></label>
              <input name="title_en" id="title_en" class="form-input"
                     placeholder="Activity title in English"
                     oninput="makeSlug()">
            </div>
            <div class="field">
              <label>Slug <small style="font-weight:300;letter-spacing:0;text-transform:none;color:var(--ink-soft)">— สร้างอัตโนมัติจากชื่อ EN</small></label>
              <div class="slug-row">
                <span class="slug-prefix">activities/</span>
                <input id="slug" class="form-input" readonly placeholder="auto-generated">
              </div>
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
              <textarea name="description_th" id="editor_th"></textarea>
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
              <textarea name="description_en" id="editor_en"></textarea>
            </div>
          </div>
        </div>

        <!-- Gallery -->
        <div class="card">
          <div class="card-header">
            <i class="bi bi-images"></i>
            <span>แกลเลอรีรูปภาพ</span>
          </div>
          <div class="card-body">
            <div class="field">
              <label>เลือกรูปภาพ <small style="font-weight:300;letter-spacing:0;text-transform:none">(เลือกได้หลายรูปพร้อมกัน)</small></label>
              <input type="file" name="gallery[]" id="galleryInput"
                     multiple class="file-input" accept="image/*">
            </div>
            <div class="gallery-preview" id="galleryPreview"></div>
            <p class="gallery-count" id="galleryCount"></p>
          </div>
        </div>

      </div><!-- /.editor-main -->

      <!-- ═══ SIDEBAR ═══ -->
      <div class="editor-sidebar">

        <!-- Publish -->
        <div class="card">
          <div class="card-header">
            <i class="bi bi-send"></i>
            <span>เผยแพร่</span>
          </div>
          <div class="card-body">
            <button type="submit" class="btn-publish">
              <i class="bi bi-check-lg"></i> บันทึกกิจกรรม
            </button>
            <a href="activity_index.php" class="btn-cancel">
              <i class="bi bi-x"></i> ยกเลิก
            </a>
            <div class="tip-box" style="margin-top:1rem">
              <i class="bi bi-info-circle"></i>
              กิจกรรมจะแสดงบนเว็บไซต์ทันทีหลังบันทึก สามารถซ่อนได้จากหน้ารายการ
            </div>
          </div>
        </div>

        <!-- Cover Image -->
        <div class="card">
          <div class="card-header">
            <i class="bi bi-image"></i>
            <span>รูปภาพหลัก</span>
          </div>
          <div class="card-body">
            <div class="img-preview-wrap" id="coverWrap">
              <div class="img-placeholder" id="coverPlaceholder">
                <i class="bi bi-cloud-upload"></i>
                <span>คลิกเลือกรูปภาพ</span>
              </div>
            </div>
            <input type="file" name="image" id="coverInput"
                   class="file-input" accept="image/*">
          </div>
        </div>

      </div><!-- /.editor-sidebar -->

    </div>
  </form>

</div>

<script>
  /* CKEditor */
  CKEDITOR.replace('editor_th', { height: 300, resize_enabled: false });
  CKEDITOR.replace('editor_en', { height: 250, resize_enabled: false });

  /* Auto slug */
  function makeSlug() {
    const val  = document.getElementById('title_en').value;
    const slug = val.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    document.getElementById('slug').value = slug;
  }

  /* Cover image preview */
  document.getElementById('coverInput').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
      const wrap = document.getElementById('coverWrap');
      wrap.classList.add('has-image');
      wrap.innerHTML = `<img id="coverPreview" src="${e.target.result}" alt="preview">`;
    };
    reader.readAsDataURL(file);
  });

  /* Gallery preview */
  document.getElementById('galleryInput').addEventListener('change', function () {
    const files   = Array.from(this.files);
    const preview = document.getElementById('galleryPreview');
    const count   = document.getElementById('galleryCount');
    preview.innerHTML = '';
    files.slice(0, 9).forEach(file => {
      const reader = new FileReader();
      reader.onload = e => {
        const img = document.createElement('img');
        img.src = e.target.result;
        preview.appendChild(img);
      };
      reader.readAsDataURL(file);
    });
    count.textContent = files.length > 0 ? `เลือกแล้ว ${files.length} รูป${files.length > 9 ? ' (แสดงตัวอย่าง 9 รูปแรก)' : ''}` : '';
  });
</script>
</body>
</html>