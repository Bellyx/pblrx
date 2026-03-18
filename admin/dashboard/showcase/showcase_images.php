<?php
require_once __DIR__.'/../../../config/db.php';
$id = (int)($_GET['id'] ?? 0);

/* ── UPLOAD MULTIPLE ── */
if (isset($_POST['upload']) && !empty($_FILES['image']['name'][0])) {
    foreach ($_FILES['image']['name'] as $k => $name) {
        $ext  = pathinfo($name, PATHINFO_EXTENSION);
        $file = time().rand(100,999).".".$ext;
        move_uploaded_file($_FILES['image']['tmp_name'][$k], "../../../assets/upload/showcase/".$file);
        $pdo->prepare("INSERT INTO showcase_images (category_id,image,media_type) VALUES (?,?,'image')")
            ->execute([$id,$file]);
    }
    header("Location: showcase_images.php?id=$id"); exit;
}

/* ── ADD YOUTUBE ── */
if (isset($_POST['add_video']) && !empty($_POST['youtube_url'])) {
    $pdo->prepare("INSERT INTO showcase_images (category_id,media_type,youtube_url) VALUES (?,?,?)")
        ->execute([$id,'video',$_POST['youtube_url']]);
    header("Location: showcase_images.php?id=$id"); exit;
}

/* ── REPLACE IMAGE ── */
if (isset($_POST['replace']) && !empty($_FILES['new_image']['name'])) {
    $imgId   = (int)$_POST['img_id'];
    $oldFile = $_POST['old_file'];
    $ext     = pathinfo($_FILES['new_image']['name'], PATHINFO_EXTENSION);
    $newFile = time().rand(100,999).".".$ext;
    move_uploaded_file($_FILES['new_image']['tmp_name'], "../../../assets/upload/showcase/".$newFile);
    if ($oldFile && file_exists("../../../assets/upload/showcase/".$oldFile))
        unlink("../../../assets/upload/showcase/".$oldFile);
    $pdo->prepare("UPDATE showcase_images SET image=? WHERE id=?")->execute([$newFile,$imgId]);
    header("Location: showcase_images.php?id=$id"); exit;
}

/* ── DELETE ── */
if (isset($_GET['delete'])) {
    $row = $pdo->prepare("SELECT * FROM showcase_images WHERE id=?");
    $row->execute([$_GET['delete']]); $row = $row->fetch();
    if ($row && $row['media_type'] === 'image') {
        $p = "../../../assets/upload/showcase/".$row['image'];
        if (file_exists($p)) unlink($p);
    }
    $pdo->prepare("DELETE FROM showcase_images WHERE id=?")->execute([$_GET['delete']]);
    header("Location: showcase_images.php?id=$id"); exit;
}

/* ── FETCH ── */
$cat = $pdo->prepare("SELECT * FROM showcase_categories WHERE id=?");
$cat->execute([$id]); $cat = $cat->fetch();

$imgs = $pdo->prepare("SELECT * FROM showcase_images WHERE category_id=? ORDER BY id DESC");
$imgs->execute([$id]); $imgs = $imgs->fetchAll();

require_once '../../../includes/header_db.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>จัดการ Showcase – PBLR Backoffice</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Noto+Sans+Thai:wght@300;400;500;600&display=swap');
    :root {
      --night:#0d0f1a; --surface:#f0f2f5; --white:#fff;
      --gold:#c8a94a; --gold-pale:#e6d49a; --gold-glow:rgba(200,169,74,.12); --gold-border:rgba(200,169,74,.22);
      --ink:#1a1c2e; --ink-mid:#4a4c62; --ink-soft:rgba(26,28,46,.42);
      --danger:#e05c5c; --danger-bg:rgba(224,92,92,.09);
      --teal:#1b7fa8; --teal-bg:rgba(27,127,168,.09);
      --radius:14px; --radius-sm:9px;
      --shadow-sm:0 2px 12px rgba(13,15,26,.07); --shadow-md:0 10px 36px rgba(13,15,26,.13);
      --ease:cubic-bezier(.4,0,.2,1); --dur:.25s;
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Noto Sans Thai',sans-serif;background:var(--surface);color:var(--ink)}
    .page-wrap{max-width:1100px;margin:0 auto;padding:2.5rem 2rem 5rem}

    .back-link{display:inline-flex;align-items:center;gap:.45rem;font-size:.8rem;color:var(--ink-soft);text-decoration:none;font-weight:500;margin-bottom:1.5rem;transition:color var(--dur),gap var(--dur)}
    .back-link:hover{color:var(--ink);gap:.7rem}

    .page-header{margin-bottom:2rem}
    .page-header h2{font-family:'DM Serif Display',serif;font-size:1.55rem;color:var(--ink);margin-bottom:.25rem;display:flex;align-items:center;gap:.5rem}
    .page-header h2 i{color:var(--gold)}
    .page-header p{font-size:.82rem;color:var(--ink-soft);font-weight:300}

    /* card */
    .card{background:var(--white);border-radius:var(--radius);border:1px solid rgba(26,28,46,.07);box-shadow:var(--shadow-sm);overflow:hidden;margin-bottom:1.5rem}
    .card-head{background:var(--night);border-bottom:1px solid var(--gold-border);padding:1rem 1.5rem;display:flex;align-items:center;gap:.65rem}
    .card-head .hicon{width:28px;height:28px;border-radius:7px;background:var(--gold-glow);border:1px solid var(--gold-border);display:flex;align-items:center;justify-content:center;font-size:.78rem;color:var(--gold);flex-shrink:0}
    .card-head span{font-family:'DM Serif Display',serif;font-size:.95rem;color:#fff}
    .card-body{padding:1.5rem}

    .forms-row{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem}
    @media(max-width:700px){.forms-row{grid-template-columns:1fr}}

    /* drop zone */
    .drop-zone{border:2px dashed rgba(26,28,46,.14);border-radius:var(--radius-sm);padding:2rem 1.25rem;text-align:center;cursor:pointer;position:relative;transition:border-color var(--dur),background var(--dur)}
    .drop-zone:hover,.drop-zone.drag{border-color:var(--gold);background:var(--gold-glow)}
    .drop-zone input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
    .drop-zone i{font-size:1.9rem;color:rgba(26,28,46,.18);display:block;margin-bottom:.6rem;transition:color var(--dur)}
    .drop-zone:hover i,.drop-zone.drag i{color:var(--gold)}
    .dz-text{font-size:.84rem;color:var(--ink-soft);font-weight:300}
    .dz-text strong{color:var(--ink);font-weight:600}
    #fileNames{margin-top:.6rem;font-size:.77rem;color:var(--gold);font-weight:500;min-height:1.2em;word-break:break-all}

    /* form inputs */
    .f-group{display:flex;flex-direction:column;gap:.35rem;margin-bottom:.85rem}
    .f-label{font-size:.7rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-soft)}
    .f-input{border:1px solid rgba(26,28,46,.12);border-radius:var(--radius-sm);padding:.6rem .85rem;font-family:'Noto Sans Thai',sans-serif;font-size:.87rem;color:var(--ink);outline:none;transition:border-color var(--dur),box-shadow var(--dur)}
    .f-input:focus{border-color:var(--gold);box-shadow:0 0 0 3px var(--gold-glow)}
    .f-input::placeholder{color:rgba(26,28,46,.28)}

    .btn{display:inline-flex;align-items:center;gap:.45rem;font-family:'Noto Sans Thai',sans-serif;font-size:.85rem;font-weight:600;padding:.62rem 1.4rem;border-radius:var(--radius-sm);border:none;cursor:pointer;transition:background var(--dur),transform var(--dur)}
    .btn:hover{transform:translateY(-1px)}
    .btn-dark{background:var(--night);color:#fff}
    .btn-dark:hover{background:var(--gold);color:var(--night)}
    .btn-right{display:flex;justify-content:flex-end;margin-top:1rem}

    /* gallery */
    .gallery-header{display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem}
    .gallery-header h3{font-family:'DM Serif Display',serif;font-size:1.05rem;color:var(--ink);display:flex;align-items:center;gap:.55rem}
    .gallery-header h3 i{color:var(--gold)}
    .count-badge{font-size:.72rem;font-weight:600;background:var(--gold-glow);border:1px solid var(--gold-border);color:var(--gold);padding:.15rem .6rem;border-radius:100px}

    .img-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem}
    @media(max-width:900px){.img-grid{grid-template-columns:repeat(3,1fr)}}
    @media(max-width:580px){.img-grid{grid-template-columns:repeat(2,1fr)}}

    .img-card{background:var(--white);border-radius:var(--radius);border:1px solid rgba(26,28,46,.07);box-shadow:var(--shadow-sm);overflow:hidden;transition:transform var(--dur),box-shadow var(--dur)}
    .img-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-md)}

    .img-thumb{position:relative;aspect-ratio:4/3;background:var(--surface);overflow:hidden}
    .img-thumb img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .5s var(--ease)}
    .img-card:hover .img-thumb img{transform:scale(1.06)}

    /* video badge */
    .vbadge{position:absolute;top:.5rem;left:.5rem;background:rgba(13,15,26,.75);backdrop-filter:blur(4px);border:1px solid rgba(255,255,255,.12);color:#fff;font-size:.62rem;font-weight:700;letter-spacing:.05em;padding:.18rem .5rem;border-radius:100px;display:flex;align-items:center;gap:.3rem}
    .vbadge i{color:#f55;font-size:.65rem}

    /* play */
    .play-over{position:absolute;inset:0;background:rgba(13,15,26,.4);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .25s;cursor:pointer}
    .img-card:hover .play-over{opacity:1}
    .play-circle{width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.15);border:2px solid rgba(255,255,255,.5);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.05rem}

    /* image overlay */
    .img-over{position:absolute;inset:0;background:rgba(13,15,26,.52);display:flex;align-items:center;justify-content:center;gap:.5rem;opacity:0;transition:opacity .25s}
    .img-card:hover .img-over{opacity:1}

    .ov-btn{width:34px;height:34px;border-radius:50%;border:1.5px solid rgba(255,255,255,.35);background:rgba(255,255,255,.1);backdrop-filter:blur(4px);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.82rem;cursor:pointer;transition:background var(--dur),border-color var(--dur),transform var(--dur)}
    .ov-btn:hover{transform:scale(1.12)}
    .ov-edit:hover{background:var(--gold);border-color:var(--gold);color:var(--night)}
    .ov-del:hover{background:var(--danger);border-color:var(--danger)}

    .img-footer{padding:.55rem .85rem;display:flex;align-items:center;justify-content:space-between;gap:.4rem}
    .img-name{font-size:.68rem;color:var(--ink-soft);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:110px;font-weight:300}
    .chip{font-size:.62rem;font-weight:700;padding:.1rem .45rem;border-radius:100px;flex-shrink:0}
    .chip-img{background:var(--teal-bg);color:var(--teal)}
    .chip-vid{background:rgba(224,92,92,.1);color:var(--danger)}

    /* delete mini btn for video */
    .del-mini{width:24px;height:24px;border-radius:6px;border:1px solid rgba(224,92,92,.25);background:rgba(224,92,92,.07);color:var(--danger);display:flex;align-items:center;justify-content:center;font-size:.72rem;cursor:pointer;transition:background var(--dur)}
    .del-mini:hover{background:var(--danger);color:#fff;border-color:var(--danger)}

    .empty-state{grid-column:1/-1;text-align:center;padding:4rem 2rem;color:var(--ink-soft)}
    .empty-state i{font-size:2.5rem;opacity:.2;display:block;margin-bottom:.75rem}
    .empty-state p{font-size:.88rem;font-weight:300}

    /* modals */
    .moverlay{display:none;position:fixed;inset:0;background:rgba(13,15,26,.8);backdrop-filter:blur(8px);z-index:999;align-items:center;justify-content:center;padding:1rem}
    .moverlay.show{display:flex}
    .mbox{background:var(--white);border-radius:var(--radius);width:100%;box-shadow:0 24px 64px rgba(0,0,0,.25);overflow:hidden;animation:slideUp .28s var(--ease) both}
    .mbox.sm{max-width:420px}
    .mbox.lg{max-width:800px}
    @keyframes slideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}
    .mhead{background:var(--night);border-bottom:1px solid var(--gold-border);padding:1.1rem 1.5rem;display:flex;align-items:center;justify-content:space-between}
    .mhead span{font-family:'DM Serif Display',serif;font-size:.95rem;color:#fff}
    .mclose{background:none;border:none;cursor:pointer;color:rgba(255,255,255,.4);font-size:1rem;transition:color var(--dur)}
    .mclose:hover{color:#fff}
    .mbody{padding:1.5rem}
    .mfoot{padding:.9rem 1.5rem;border-top:1px solid rgba(26,28,46,.07);display:flex;gap:.5rem;justify-content:flex-end}
    .rep-preview{width:100%;aspect-ratio:16/9;object-fit:cover;border-radius:var(--radius-sm);margin-bottom:1rem;border:1px solid rgba(26,28,46,.08)}
    .btn-save{display:inline-flex;align-items:center;gap:.4rem;background:var(--night);color:#fff;font-family:'Noto Sans Thai',sans-serif;font-size:.85rem;font-weight:600;padding:.6rem 1.4rem;border-radius:var(--radius-sm);border:none;cursor:pointer;transition:background var(--dur)}
    .btn-save:hover{background:var(--gold);color:var(--night)}
    .btn-cancel{background:transparent;color:var(--ink-soft);font-family:'Noto Sans Thai',sans-serif;font-size:.85rem;padding:.6rem 1.2rem;border-radius:var(--radius-sm);border:1px solid rgba(26,28,46,.12);cursor:pointer;transition:background var(--dur)}
    .btn-cancel:hover{background:var(--surface)}
    .vid-wrap{aspect-ratio:16/9}
    .vid-wrap iframe{width:100%;height:100%;border:none;display:block}
  </style>
</head>
<body>
<div class="page-wrap">

  <a href="showcase_cms.php" class="back-link"><i class="bi bi-arrow-left"></i> กลับหน้า Showcase CMS</a>

  <div class="page-header">
    <h2><i class="bi bi-images"></i> จัดการ Showcase<?php if($cat): ?> — <span style="color:var(--ink-mid);font-size:1.2rem"><?= htmlspecialchars($cat['title_th']) ?></span><?php endif; ?></h2>
    <p>อัพโหลดรูปภาพและเพิ่มวิดีโอ YouTube</p>
  </div>

  <div class="forms-row">

    <!-- Upload Images -->
    <div class="card">
      <div class="card-head">
        <span class="hicon"><i class="bi bi-cloud-upload"></i></span>
        <span>อัพโหลดรูปภาพ</span>
      </div>
      <div class="card-body">
        <form method="post" enctype="multipart/form-data">
          <div class="drop-zone" id="dz">
            <input type="file" name="image[]" id="fi" accept="image/*" multiple>
            <i class="bi bi-cloud-arrow-up"></i>
            <div class="dz-text"><strong>คลิกหรือลากรูปมาวาง</strong><br><span style="font-size:.74rem">PNG · JPG · WEBP — หลายไฟล์พร้อมกันได้</span></div>
            <div id="fn"></div>
          </div>
          <div class="btn-right">
            <button type="submit" name="upload" class="btn btn-dark" id="ub" disabled>
              <i class="bi bi-upload"></i> อัพโหลด
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- YouTube -->
    <div class="card">
      <div class="card-head">
        <span class="hicon"><i class="bi bi-youtube"></i></span>
        <span>เพิ่ม YouTube Video</span>
      </div>
      <div class="card-body">
        <form method="post">
          <div class="f-group">
            <label class="f-label">YouTube URL</label>
            <input class="f-input" type="text" name="youtube_url" placeholder="https://youtube.com/watch?v=xxxx">
          </div>
          <div class="btn-right">
            <button type="submit" name="add_video" class="btn btn-dark">
              <i class="bi bi-plus-lg"></i> เพิ่มวิดีโอ
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>

  <!-- Gallery -->
  <div class="gallery-header">
    <h3><i class="bi bi-grid-3x3-gap-fill"></i> สื่อทั้งหมด</h3>
    <span class="count-badge"><?= count($imgs) ?> รายการ</span>
  </div>

  <div class="img-grid">
    <?php if (empty($imgs)): ?>
      <div class="empty-state">
        <i class="bi bi-image"></i>
        <p>ยังไม่มีสื่อ กรุณาอัพโหลดรูปหรือเพิ่มวิดีโอ</p>
      </div>
    <?php else: foreach ($imgs as $i):
      $isVideo = ($i['media_type'] === 'video');
      preg_match('/(?:v=|youtu\.be\/)([^&\s]+)/', $i['youtube_url'] ?? '', $m);
      $vid   = $m[1] ?? '';
      $thumb = $isVideo
        ? "https://img.youtube.com/vi/{$vid}/hqdefault.jpg"
        : "../../../assets/upload/showcase/".htmlspecialchars($i['image'] ?? '');
    ?>
      <div class="img-card">
        <div class="img-thumb">
          <img src="<?= $thumb ?>" alt="" loading="lazy">

          <?php if ($isVideo): ?>
            <div class="vbadge"><i class="bi bi-youtube"></i> YouTube</div>
            <div class="play-over" data-vid="<?= htmlspecialchars($vid) ?>">
              <div class="play-circle"><i class="bi bi-play-fill"></i></div>
            </div>
          <?php else: ?>
            <div class="img-over">
              <button class="ov-btn ov-edit btn-replace"
                data-id="<?= $i['id'] ?>"
                data-file="<?= htmlspecialchars($i['image'] ?? '') ?>"
                data-src="<?= $thumb ?>"
                title="แก้ไขรูป"><i class="bi bi-pencil"></i></button>
              <button class="ov-btn ov-del btn-del" data-id="<?= $i['id'] ?>" title="ลบ">
                <i class="bi bi-trash"></i></button>
            </div>
          <?php endif; ?>
        </div>

        <div class="img-footer">
          <span class="img-name"><?= $isVideo ? $vid : htmlspecialchars($i['image'] ?? '') ?></span>
          <div style="display:flex;align-items:center;gap:.35rem;flex-shrink:0">
            <span class="chip <?= $isVideo ? 'chip-vid' : 'chip-img' ?>"><?= $isVideo ? 'VDO' : 'IMG' ?></span>
            <?php if ($isVideo): ?>
              <button class="del-mini btn-del" data-id="<?= $i['id'] ?>"><i class="bi bi-trash"></i></button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>

</div>

<!-- Replace Modal -->
<div class="moverlay" id="repModal">
  <div class="mbox sm">
    <div class="mhead"><span>แก้ไขรูปภาพ</span><button class="mclose" id="closeRep"><i class="bi bi-x-lg"></i></button></div>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="img_id"   id="rep_id">
      <input type="hidden" name="old_file" id="rep_old">
      <div class="mbody">
        <img id="rep_img" class="rep-preview" src="" alt="">
        <div class="f-group">
          <label class="f-label">เลือกรูปใหม่</label>
          <input type="file" name="new_image" class="f-input" accept="image/*" required>
        </div>
      </div>
      <div class="mfoot">
        <button type="submit" name="replace" class="btn-save"><i class="bi bi-check-lg"></i> บันทึก</button>
        <button type="button" class="btn-cancel" id="cancelRep">ยกเลิก</button>
      </div>
    </form>
  </div>
</div>

<!-- Video Modal -->
<div class="moverlay" id="vidModal">
  <div class="mbox lg">
    <div class="mhead"><span>YouTube Video</span><button class="mclose" id="closeVid"><i class="bi bi-x-lg"></i></button></div>
    <div class="mbody" style="padding:0"><div class="vid-wrap"><iframe id="vidFrame" allowfullscreen></iframe></div></div>
  </div>
</div>

<script>
  /* drop zone */
  const dz=document.getElementById('dz'),fi=document.getElementById('fi'),fn=document.getElementById('fn'),ub=document.getElementById('ub');
  fi.addEventListener('change',()=>{const f=[...fi.files];fn.textContent=f.length?f.map(x=>x.name).join(', '):'';ub.disabled=!f.length});
  ['dragover','dragenter'].forEach(e=>dz.addEventListener(e,ev=>{ev.preventDefault();dz.classList.add('drag')}));
  ['dragleave','drop'].forEach(e=>dz.addEventListener(e,()=>dz.classList.remove('drag')));

  /* delete */
  document.querySelectorAll('.btn-del').forEach(b=>{
    b.addEventListener('click',()=>{
      Swal.fire({title:'ลบสื่อนี้?',icon:'warning',showCancelButton:true,confirmButtonColor:'#e05c5c',cancelButtonColor:'#4a4c62',confirmButtonText:'ลบเลย',cancelButtonText:'ยกเลิก'})
      .then(r=>{if(r.isConfirmed)location.href=`?id=<?= $id ?>&delete=${b.dataset.id}`});
    });
  });

  /* replace */
  const repMod=document.getElementById('repModal');
  document.querySelectorAll('.btn-replace').forEach(b=>{
    b.addEventListener('click',()=>{
      document.getElementById('rep_id').value=b.dataset.id;
      document.getElementById('rep_old').value=b.dataset.file;
      document.getElementById('rep_img').src=b.dataset.src;
      repMod.classList.add('show');
    });
  });
  document.getElementById('closeRep').addEventListener('click',()=>repMod.classList.remove('show'));
  document.getElementById('cancelRep').addEventListener('click',()=>repMod.classList.remove('show'));
  repMod.addEventListener('click',e=>{if(e.target===repMod)repMod.classList.remove('show')});

  /* video */
  const vidMod=document.getElementById('vidModal'),vidFrame=document.getElementById('vidFrame');
  function openVid(vid){vidFrame.src=`https://www.youtube.com/embed/${vid}?autoplay=1`;vidMod.classList.add('show')}
  function closeVid(){vidMod.classList.remove('show');vidFrame.src=''}
  document.querySelectorAll('.play-over').forEach(el=>{el.addEventListener('click',()=>openVid(el.dataset.vid))});
  document.getElementById('closeVid').addEventListener('click',closeVid);
  vidMod.addEventListener('click',e=>{if(e.target===vidMod)closeVid()});
</script>
</body>
</html>