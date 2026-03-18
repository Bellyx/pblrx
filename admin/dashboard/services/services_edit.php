<?php
require_once __DIR__ . '/../../../config/db.php';
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM services WHERE id=?");
$stmt->execute([$id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$service) { die("Service not found"); }

/* SAVE */
if (isset($_POST['save'])) {
  $image = $service['image'];
  if (!empty($_FILES['image']['name'])) {

    $image = time() . '_' . $_FILES['image']['name'];

    move_uploaded_file(
      $_FILES['image']['tmp_name'],
      "../../../assets/upload/services/" . $image
    );
  }

  $stmt = $pdo->prepare("
  UPDATE services
  SET
  title_th=?,
  title_en=?,
  icon=?,
  image=?,
  video_url=?,
  link_url=?,
  content_th=?,
  content_en=?
  WHERE id=?
  ");

  $stmt->execute([
    $_POST['title_th'],
    $_POST['title_en'],
    $_POST['icon'],
    $image,
    $_POST['video_url'],
    $_POST['link_url'],
    $_POST['content_th'],
    $_POST['content_en'],
    $id
  ]);

  header("Location: services_cms.php");
  exit;
}
require_once '../../../includes/header_db.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>แก้ไขบริการ – PBLR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
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
      --success:     #3db88a;
      --radius:      13px;
      --radius-sm:   8px;
      --shadow-sm:   0 2px 12px rgba(13,15,26,0.07);
      --shadow-md:   0 8px 32px rgba(13,15,26,0.12);
      --ease:        cubic-bezier(0.4,0,0.2,1);
      --dur:         0.22s;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Noto Sans Thai', sans-serif; background: var(--surface); color: var(--ink); }

    .main-content { margin-left: var(--sb-width); min-height: 100vh; padding: 2.5rem 2.5rem 5rem; }
    @media (max-width: 991px) { .main-content { margin-left: 0; padding: 1.5rem 1rem 4rem; } }

    /* ── Header ── */
    .page-header {
      display: flex; align-items: center; gap: 1rem;
      margin-bottom: 2rem; padding-bottom: 1.25rem;
      border-bottom: 1px solid var(--ink-ghost); flex-wrap: wrap;
    }

    .back-btn {
      display: inline-flex; align-items: center; gap: 0.4rem;
      font-size: 0.82rem; color: var(--ink-soft); text-decoration: none;
      padding: 0.45rem 0.9rem; border: 1px solid rgba(26,28,46,0.12);
      border-radius: var(--radius-sm); background: var(--white);
      transition: color var(--dur), border-color var(--dur);
    }
    .back-btn:hover { color: var(--ink); border-color: var(--ink); }

    .page-title {
      font-family: 'DM Serif Display', serif; font-size: 1.45rem;
      color: var(--ink); display: flex; align-items: center; gap: 0.6rem;
    }

    .h-icon {
      width: 32px; height: 32px; background: var(--gold-glow);
      border: 1px solid var(--gold-border); border-radius: var(--radius-sm);
      display: inline-flex; align-items: center; justify-content: center;
      font-size: 0.85rem; color: var(--gold);
    }

    /* ── Layout ── */
    .editor-layout {
      display: grid; grid-template-columns: 1fr 280px;
      gap: 1.5rem; align-items: start;
    }
    @media (max-width: 860px) { .editor-layout { grid-template-columns: 1fr; } .editor-sidebar { order: -1; } }

    /* ── Card ── */
    .card {
      background: var(--white); border-radius: var(--radius);
      border: 1px solid rgba(26,28,46,0.07); box-shadow: var(--shadow-sm);
      overflow: hidden; margin-bottom: 1.25rem;
    }

    .card-header {
      display: flex; align-items: center; gap: 0.6rem;
      padding: 0.95rem 1.4rem; background: var(--night);
      border-bottom: 1px solid rgba(200,169,74,0.12);
    }

    .card-header i {
      width: 26px; height: 26px; background: var(--gold-glow);
      border: 1px solid var(--gold-border); border-radius: 6px;
      display: flex; align-items: center; justify-content: center;
      font-size: 0.72rem; color: var(--gold); flex-shrink: 0;
    }

    .card-header span { font-family: 'DM Serif Display', serif; font-size: 0.92rem; color: #fff; }
    .card-body { padding: 1.4rem; }

    /* ── Fields ── */
    .field { margin-bottom: 1.1rem; }
    .field:last-child { margin-bottom: 0; }

    label.fl {
      display: block; font-size: 0.7rem; font-weight: 700;
      letter-spacing: 0.09em; text-transform: uppercase;
      color: var(--ink-soft); margin-bottom: 0.4rem;
    }

    .lang-tag {
      display: inline-block; font-size: 0.65rem; font-weight: 700;
      letter-spacing: 0.1em; padding: 0.15rem 0.5rem;
      border-radius: 4px; margin-left: 0.4rem;
    }
    .lang-tag.th { background: rgba(61,184,138,0.12); color: var(--success); }
    .lang-tag.en { background: rgba(59,130,246,0.1);  color: #3b82f6; }

    .form-input {
      width: 100%; padding: 0.65rem 0.9rem;
      border: 1px solid rgba(26,28,46,0.12); border-radius: var(--radius-sm);
      font-family: 'Noto Sans Thai', sans-serif; font-size: 0.88rem;
      color: var(--ink); background: var(--white); outline: none;
      transition: border-color var(--dur), box-shadow var(--dur);
    }
    .form-input.lg { font-size: 1rem; padding: 0.75rem 0.9rem; font-weight: 600; }
    .form-input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px var(--gold-glow); }
    .form-input[type="file"] { padding: 0.5rem 0.75rem; cursor: pointer; }

    /* icon row */
    .icon-row { display: flex; align-items: center; gap: 0.65rem; }
    .icon-row .form-input { flex: 1; }
    .icon-box {
      width: 40px; height: 40px; border-radius: var(--radius-sm);
      background: var(--surface); border: 1px solid rgba(26,28,46,0.1);
      display: flex; align-items: center; justify-content: center;
      overflow: hidden; flex-shrink: 0;
    }
    .icon-box img { width: 100%; height: 100%; object-fit: contain; }
    .icon-box .ph { font-size: 1rem; color: rgba(26,28,46,0.2); }

    /* image preview */
    .img-preview-wrap {
      margin-top: 0.65rem; border-radius: var(--radius-sm); overflow: hidden;
      background: var(--surface); border: 1.5px dashed rgba(26,28,46,0.15);
      min-height: 90px; display: flex; align-items: center; justify-content: center;
      transition: border-color var(--dur);
    }
    .img-preview-wrap.has-img { border-style: solid; border-color: var(--gold-border); }
    .img-preview-wrap img { width: 100%; display: block; max-height: 180px; object-fit: cover; border-radius: var(--radius-sm); }
    .img-ph { display: flex; flex-direction: column; align-items: center; gap: 4px; font-size: 0.76rem; color: rgba(26,28,46,0.25); padding: 1.2rem; }
    .img-ph i { font-size: 1.6rem; }

    /* video preview */
    .video-wrap { margin-top: 0.65rem; }
    .video-frame { width: 100%; aspect-ratio: 16/9; border-radius: var(--radius-sm); border: none; background: var(--surface); display: none; }
    .video-frame.show { display: block; }

    /* TinyMCE wrapper */
    .tmce-wrap { border-radius: var(--radius-sm); overflow: hidden; }

    /* ── Sidebar ── */
    .btn-save {
      width: 100%; display: flex; align-items: center; justify-content: center;
      gap: 0.5rem; background: var(--night); color: #fff;
      font-family: 'Noto Sans Thai', sans-serif; font-size: 0.88rem; font-weight: 600;
      padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: none;
      cursor: pointer; transition: background var(--dur), transform var(--dur), box-shadow var(--dur);
      margin-bottom: 0.6rem;
    }
    .btn-save:hover { background: var(--gold); color: var(--night); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(200,169,74,0.28); }

    .btn-back-list {
      width: 100%; display: flex; align-items: center; justify-content: center;
      gap: 0.5rem; background: transparent; color: var(--ink-mid);
      font-family: 'Noto Sans Thai', sans-serif; font-size: 0.84rem;
      padding: 0.65rem 1rem; border-radius: var(--radius-sm);
      border: 1px solid rgba(26,28,46,0.12); text-decoration: none;
      transition: background var(--dur);
    }
    .btn-back-list:hover { background: var(--surface); }

    /* meta rows */
    .meta-row {
      display: flex; align-items: center; justify-content: space-between;
      font-size: 0.76rem; color: var(--ink-soft);
      padding: 0.5rem 0; border-bottom: 1px solid var(--ink-ghost);
    }
    .meta-row:last-child { border-bottom: none; }
    .meta-row strong { color: var(--ink-mid); font-weight: 500; }
  </style>
</head>
<body>

<div class="main-content">

  <!-- Header -->
  <div class="page-header">
    <a href="services_cms.php" class="back-btn">
      <i class="bi bi-arrow-left"></i> กลับ
    </a>
    <h1 class="page-title">
      <span class="h-icon"><i class="bi bi-pencil-square"></i></span>
      แก้ไขบริการ
    </h1>
  </div>

  <form method="post" enctype="multipart/form-data" id="editForm">
    <div class="editor-layout">

      <!-- ═══ MAIN ═══ -->
      <div class="editor-main">

        <!-- Titles -->
        <div class="card">
          <div class="card-header">
            <i class="bi bi-type"></i>
            <span>ชื่อบริการ</span>
          </div>
          <div class="card-body">
            <div class="field">
              <label class="fl">ชื่อบริการ <span class="lang-tag th">TH</span></label>
              <input name="title_th" class="form-input lg"
                     placeholder="ชื่อบริการภาษาไทย"
                     value="<?= htmlspecialchars($service['title_th']) ?>">
            </div>
            <div class="field">
              <label class="fl">ชื่อบริการ <span class="lang-tag en">EN</span></label>
              <input name="title_en" class="form-input"
                     placeholder="Service name in English"
                     value="<?= htmlspecialchars($service['title_en']) ?>">
            </div>
          </div>
        </div>

        <!-- content_th (TinyMCE) -->
        <div class="card">
          <div class="card-header">
            <i class="bi bi-file-text"></i>
            <span>รายละเอียดบริการ</span>
          </div>
          <div class="card-body">
            <div class="tmce-wrap">
              <textarea name="content_th"><?= $service['content_th'] ?? '' ?></textarea>
            </div>
          </div>
        </div>

        <!-- Media -->
        <div class="card">
          <div class="card-header">
            <i class="bi bi-images"></i>
            <span>Icon &amp; รูปภาพ</span>
          </div>
          <div class="card-body">
            <div class="field">
              <label class="fl">Icon URL</label>
              <div class="icon-row">
                <input name="icon" id="iconInput" class="form-input"
                       placeholder="https://...icon.svg"
                       value="<?= htmlspecialchars($service['icon'] ?? '') ?>">
                <div class="icon-box" id="iconBox">
                  <?php if ($service['icon']): ?>
                    <img id="iconPreview" src="<?= htmlspecialchars($service['icon']) ?>" alt="">
                  <?php else: ?>
                    <span class="ph bi bi-image" id="iconPh"></span>
                    <img id="iconPreview" src="" style="display:none">
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="field">
              <label class="fl">รูปภาพปก (Cover Image)</label>
              <input type="file" name="image" id="imageInput" class="form-input" accept="image/*">
              <div class="img-preview-wrap <?= $service['image'] ? 'has-img' : '' ?>" id="imgPreviewWrap">
                <?php if ($service['image']): ?>
                  <img id="imgPreview" src="/PBLR/assets/upload/services/<?= htmlspecialchars($service['image']) ?>" alt="">
                <?php else: ?>
                  <div class="img-ph" id="imgPh"><i class="bi bi-cloud-upload"></i><span>เลือกรูปภาพ</span></div>
                  <img id="imgPreview" src="" style="display:none">
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Links & Video -->
        <div class="card">
          <div class="card-header">
            <i class="bi bi-link-45deg"></i>
            <span>Video &amp; ลิงก์</span>
          </div>
          <div class="card-body">
            <div class="field">
              <label class="fl">Video URL <small style="font-weight:300;letter-spacing:0;text-transform:none;color:var(--ink-soft)">(YouTube embed)</small></label>
              <input name="video_url" id="videoInput" class="form-input"
                     placeholder="https://www.youtube.com/embed/..."
                     value="<?= htmlspecialchars($service['video_url'] ?? '') ?>">
              <div class="video-wrap">
                <iframe id="videoPreview" class="video-frame <?= ($service['video_url'] ?? '') ? 'show' : '' ?>"
                        src="<?= htmlspecialchars($service['video_url'] ?? '') ?>"
                        allowfullscreen></iframe>
              </div>
            </div>
            <div class="field">
              <label class="fl">Link URL</label>
              <input name="link_url" class="form-input"
                     placeholder="https://..."
                     value="<?= htmlspecialchars($service['link_url'] ?? '') ?>">
            </div>
          </div>
        </div>

      </div><!-- /.editor-main -->

      <!-- ═══ SIDEBAR ═══ -->
      <div class="editor-sidebar">

        <!-- Save -->
        <div class="card">
          <div class="card-header">
            <i class="bi bi-floppy"></i>
            <span>บันทึก</span>
          </div>
          <div class="card-body">
            <button type="submit" name="save" class="btn-save">
              <i class="bi bi-check-lg"></i> บันทึกการแก้ไข
            </button>
            <a href="services_cms.php" class="btn-back-list">
              <i class="bi bi-arrow-left"></i> ยกเลิก
            </a>
            <div style="margin-top:1rem">
              <div class="meta-row">
                <span>ID</span><strong>#<?= $id ?></strong>
              </div>
              <div class="meta-row">
                <span>Slug</span>
                <strong style="font-family:monospace;font-size:.72rem"><?= htmlspecialchars($service['slug'] ?? '') ?></strong>
              </div>
              <div class="meta-row">
                <span>สถานะ</span>
                <strong style="color:<?= $service['is_active'] ? 'var(--success)' : 'var(--ink-soft)' ?>">
                  <?= $service['is_active'] ? 'เปิดใช้งาน' : 'ปิดอยู่' ?>
                </strong>
              </div>
            </div>
          </div>
        </div>

        <!-- Sort order -->
        <div class="card">
          <div class="card-header">
            <i class="bi bi-sort-numeric-up"></i>
            <span>ลำดับการแสดง</span>
          </div>
          <div class="card-body">
            <div class="field">
              <label class="fl">Sort Order</label>
              <input type="number" name="sort_order" class="form-input"
                     min="0"
                     value="<?= (int)($service['sort_order'] ?? 0) ?>">
            </div>
          </div>
        </div>

      </div>

    </div>
  </form>

</div>

<script>
  /* TinyMCE */
  tinymce.init({
    selector: 'textarea',
    height: 320,
    plugins: 'link image table lists code',
    toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
    skin: 'oxide',
    content_css: 'default',
    font_family_formats: 'Noto Sans Thai=Noto Sans Thai,sans-serif; Arial=arial,helvetica,sans-serif',
    resize: false,
  });

  /* Icon URL preview */
  document.getElementById('iconInput').addEventListener('input', function () {
    const img = document.getElementById('iconPreview');
    const ph  = document.getElementById('iconPh');
    if (this.value) {
      img.src = this.value;
      img.style.display = 'block';
      if (ph) ph.style.display = 'none';
    } else {
      img.style.display = 'none';
      if (ph) ph.style.display = 'block';
    }
  });

  /* Cover image preview */
  document.getElementById('imageInput').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
      const wrap = document.getElementById('imgPreviewWrap');
      const img  = document.getElementById('imgPreview');
      const ph   = document.getElementById('imgPh');
      img.src = e.target.result;
      img.style.display = 'block';
      if (ph) ph.style.display = 'none';
      wrap.classList.add('has-img');
    };
    reader.readAsDataURL(file);
  });

  /* Video URL preview */
  document.getElementById('videoInput').addEventListener('input', function () {
    const frame = document.getElementById('videoPreview');
    if (this.value) { frame.src = this.value; frame.classList.add('show'); }
    else { frame.src = ''; frame.classList.remove('show'); }
  });
</script>
</body>
</html>