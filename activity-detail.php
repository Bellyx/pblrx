<?php
include __DIR__.'/config/db.php';
$slug = $_GET['slug'] ?? '';
if (!$slug) { header("Location: activities.php"); exit; }

$stmt = $pdo->prepare("SELECT * FROM activities WHERE slug=? AND status=1 LIMIT 1");
$stmt->execute([$slug]);
$activity = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$activity) { header("Location: activities.php"); exit; }

$imgs = $pdo->prepare("SELECT * FROM activity_images WHERE activity_id=? ORDER BY id ASC");
$imgs->execute([$activity['id']]);
$images = array_values(array_filter($imgs->fetchAll(PDO::FETCH_ASSOC), fn($i) => !empty($i['image_path'])));

require_once __DIR__.'/includes/header.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($activity['title_th']) ?> – PBL-R Thailand</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Noto+Sans+Thai:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --night:       #0d0f1a;
      --surface:     #f8f7f4;
      --white:       #ffffff;
      --gold:        #c8a94a;
      --gold-pale:   #e6d49a;
      --gold-glow:   rgba(200,169,74,0.13);
      --gold-border: rgba(200,169,74,0.25);
      --ink:         #1a1c2e;
      --ink-mid:     #4a4c62;
      --ink-soft:    rgba(26,28,46,0.45);
      --radius:      14px;
      --radius-sm:   9px;
      --shadow-sm:   0 2px 16px rgba(13,15,26,0.07);
      --shadow-md:   0 10px 40px rgba(13,15,26,0.13);
      --ease:        cubic-bezier(0.4,0,0.2,1);
    }
    *,*::before,*::after { box-sizing:border-box; margin:0; padding:0 }
    body { font-family:'Noto Sans Thai',sans-serif; background:var(--surface); color:var(--ink) }

    /* ── PAGE HEADER (dark, text only) ── */
    .act-header {
      background: var(--night);
      padding: 4.5rem 2rem 3rem;
      position: relative; overflow: hidden;
    }

    .act-header::before {
      content: '';
      position: absolute; inset: 0;
      background:
        radial-gradient(ellipse 70% 80% at 80% 110%, rgba(200,169,74,.12) 0%, transparent 60%),
        radial-gradient(ellipse 40% 50% at 5% 10%,  rgba(27,127,168,.07) 0%, transparent 55%);
    }

    .act-header::after {
      content: '';
      position: absolute; inset: 0;
      background-image: radial-gradient(rgba(255,255,255,.035) 1px, transparent 1px);
      background-size: 28px 28px;
    }

    .act-header-inner {
      position: relative; z-index: 2;
      max-width: 860px; margin: 0 auto;
    }

    .back-link {
      display: inline-flex; align-items: center; gap: .45rem;
      font-size: .72rem; font-weight: 600; letter-spacing: .1em;
      text-transform: uppercase;
      color: rgba(255,255,255,.4); text-decoration: none;
      border: 1px solid rgba(255,255,255,.12);
      background: rgba(255,255,255,.05);
      padding: .28rem .85rem; border-radius: 100px;
      margin-bottom: 1.75rem;
      transition: color .2s, background .2s, border-color .2s;
      backdrop-filter: blur(4px);
    }
    .back-link:hover {
      color: var(--gold-pale);
      background: rgba(200,169,74,.1);
      border-color: var(--gold-border);
    }
    .back-link svg { width: 12px; height: 12px; }

    .act-header h1 {
      font-family: 'DM Serif Display', serif;
      font-size: clamp(1.8rem, 4vw, 2.9rem);
      color: #fff; font-weight: 400;
      line-height: 1.2; margin-bottom: 1.25rem;
    }

    .act-meta {
      display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap;
    }

    .act-meta-chip {
      display: inline-flex; align-items: center; gap: .4rem;
      font-size: .78rem; color: rgba(255,255,255,.4); font-weight: 300;
    }
    .act-meta-chip svg { width: 13px; height: 13px; }

    .gold-rule {
      width: 40px; height: 2px;
      background: linear-gradient(90deg, var(--gold), transparent);
      margin-top: 2rem; border-radius: 1px;
    }

    /* ── BODY ── */
    .act-body {
      max-width: 860px; margin: 0 auto;
      padding: 3rem 2rem 6rem;
    }

    /* ── CONTENT CARD ── */
    .act-content {
      background: var(--white);
      border-radius: var(--radius);
      border: 1px solid rgba(26,28,46,.07);
      box-shadow: var(--shadow-sm);
      padding: 2.5rem 3rem;
      margin-bottom: 3rem;
      position: relative; overflow: hidden;
    }

    .act-content::before {
      content: '';
      position: absolute; top: 0; left: 0;
      width: 4px; height: 100%;
      background: linear-gradient(to bottom, var(--gold), transparent);
    }

    .act-content p    { font-size: 1rem; color: var(--ink-mid); line-height: 1.9; font-weight: 300; margin-bottom: .9rem }
    .act-content h2, .act-content h3 { font-family:'DM Serif Display',serif; color:var(--ink); margin:1.5rem 0 .75rem }
    .act-content ul, .act-content ol { padding-left:1.5rem; margin-bottom:.9rem; color:var(--ink-mid); font-size:1rem; line-height:1.8; font-weight:300 }
    .act-content li  { margin-bottom:.35rem }
    .act-content strong { color:var(--ink); font-weight:600 }

    /* ── GALLERY ── */
    .gallery-section { margin-bottom: 2rem }

    .gallery-head {
      display: flex; align-items: center; gap: .75rem;
      margin-bottom: 1.5rem;
    }
    .gallery-head h3 {
      font-family: 'DM Serif Display', serif;
      font-size: 1.25rem; color: var(--ink);
    }
    .gallery-head .g-line { flex:1; height:1px; background:linear-gradient(90deg,rgba(200,169,74,.3),transparent) }
    .gallery-head .g-count {
      font-size:.72rem; font-weight:600;
      background:var(--gold-glow); border:1px solid var(--gold-border);
      color:var(--gold); padding:.15rem .6rem; border-radius:100px;
    }

    /* Grid — always 4 cols, auto-rows */
    .gallery-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: .75rem;
    }

    .g-item {
      border-radius: var(--radius-sm);
      overflow: hidden; cursor: pointer;
      position: relative; aspect-ratio: 1/1;
      background: rgba(26,28,46,.06);
      box-shadow: var(--shadow-sm);
      transition: transform .32s var(--ease), box-shadow .32s var(--ease);
    }
    .g-item:hover { transform: translateY(-3px) scale(1.01); box-shadow: var(--shadow-md) }
    .g-item img { width:100%; height:100%; object-fit:cover; display:block; transition:transform .5s var(--ease) }
    .g-item:hover img { transform: scale(1.07) }

    .g-overlay {
      position: absolute; inset: 0;
      background: rgba(13,15,26,.45);
      opacity: 0; transition: opacity .25s;
      display: flex; align-items: center; justify-content: center;
    }
    .g-item:hover .g-overlay { opacity: 1 }
    .g-overlay-icon {
      width: 36px; height: 36px; border-radius: 50%;
      border: 1.5px solid rgba(255,255,255,.5);
      background: rgba(255,255,255,.12); backdrop-filter:blur(4px);
      display: flex; align-items: center; justify-content: center;
      color: #fff;
    }

    /* hidden items */
    .g-item.hidden { display: none }
    .g-item.hidden.revealed { display: block }

    /* Show more button */
    .show-more-wrap {
      text-align: center;
      margin-top: 1.25rem;
    }

    .btn-show-more {
      display: inline-flex; align-items: center; gap: .5rem;
      background: var(--white);
      border: 1.5px solid rgba(26,28,46,.13);
      color: var(--ink-mid);
      font-family: 'Noto Sans Thai', sans-serif;
      font-size: .85rem; font-weight: 600;
      padding: .65rem 1.6rem; border-radius: 100px;
      cursor: pointer;
      transition: background .2s, border-color .2s, color .2s, transform .2s;
      box-shadow: var(--shadow-sm);
    }
    .btn-show-more:hover {
      border-color: var(--gold-border);
      background: var(--gold-glow);
      color: var(--ink);
      transform: translateY(-1px);
    }
    .btn-show-more svg { width: 15px; height: 15px; transition: transform .3s }
    .btn-show-more.open svg { transform: rotate(180deg) }

    /* ── LIGHTBOX ── */
    .lightbox { display:none; position:fixed; inset:0; background:rgba(13,15,26,.96); backdrop-filter:blur(14px); z-index:9999; align-items:center; justify-content:center; flex-direction:column }
    .lightbox.open { display:flex }
    .lb-wrap { max-width:90vw; max-height:80vh; animation:lbIn .28s var(--ease) both }
    @keyframes lbIn { from{opacity:0;transform:scale(.93)} to{opacity:1;transform:none} }
    .lb-wrap img { max-width:90vw; max-height:80vh; border-radius:var(--radius); display:block; box-shadow:0 32px 80px rgba(0,0,0,.5) }
    .lb-close { position:fixed; top:1.5rem; right:1.5rem; width:42px; height:42px; border-radius:50%; background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2); color:#fff; font-size:1.1rem; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .2s,transform .2s }
    .lb-close:hover { background:rgba(255,255,255,.2); transform:rotate(90deg) }
    .lb-nav { position:fixed; top:50%; transform:translateY(-50%); width:44px; height:44px; border-radius:50%; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.15); color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .2s }
    .lb-nav:hover { background:rgba(200,169,74,.25); border-color:var(--gold-border) }
    .lb-prev { left:1.5rem } .lb-next { right:1.5rem }
    .lb-counter { margin-top:1.25rem; font-size:.78rem; color:rgba(255,255,255,.35); letter-spacing:.08em }

    @media(max-width:640px) {
      .act-header { padding: 3.5rem 1.25rem 2.5rem }
      .act-body   { padding: 2rem 1rem 4rem }
      .act-content { padding: 1.75rem 1.5rem }
      .gallery-grid { grid-template-columns: repeat(3,1fr) }
    }
    @media(max-width:420px) {
      .gallery-grid { grid-template-columns: repeat(2,1fr) }
    }
  </style>
</head>
<body>

<!-- ── HEADER (text only) ── -->
<div class="act-header">
  <div class="act-header-inner">
    <a href="activities.php" class="back-link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
      กิจกรรมทั้งหมด
    </a>
    <h1><?= htmlspecialchars($activity['title_th']) ?></h1>
    <div class="act-meta">
      <span class="act-meta-chip">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        <?= date('d F Y', strtotime($activity['created_at'])) ?>
      </span>
      <?php if (!empty($images)): ?>
      <span class="act-meta-chip">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
        <?= count($images) ?> ภาพกิจกรรม
      </span>
      <?php endif; ?>
    </div>
    <div class="gold-rule"></div>
  </div>
</div>

<!-- ── BODY ── -->
<div class="act-body">

  <!-- Content -->
  <?php if (!empty($activity['description_th'])): ?>
  <div class="act-content">
    <?= $activity['description_th'] ?>
  </div>
  <?php endif; ?>

  <!-- Gallery -->
  <?php if (!empty($images)):
    $total   = count($images);
    $limit   = 8;
    $hasMore = $total > $limit;
  ?>
  <div class="gallery-section">

    <div class="gallery-head">
      <h3>ภาพกิจกรรม</h3>
      <span class="g-count"><?= $total ?> รูป</span>
      <div class="g-line"></div>
    </div>

    <div class="gallery-grid" id="galleryGrid">
      <?php foreach ($images as $k => $img): ?>
        <div class="g-item <?= $k >= $limit ? 'hidden' : '' ?>"
          data-src="assets/upload/activities/<?= htmlspecialchars($img['image_path']) ?>"
          data-index="<?= $k ?>">
          <img src="assets/upload/activities/<?= htmlspecialchars($img['image_path']) ?>"
               alt="ภาพกิจกรรม <?= $k+1 ?>" loading="lazy">
          <div class="g-overlay">
            <div class="g-overlay-icon">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($hasMore): ?>
    <div class="show-more-wrap">
      <button class="btn-show-more" id="showMoreBtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        ดูรูปทั้งหมด (<?= $total - $limit ?> รูปที่เหลือ)
      </button>
    </div>
    <?php endif; ?>

  </div>
  <?php endif; ?>

</div>

<!-- Lightbox -->
<div class="lightbox" id="lightbox">
  <button class="lb-close" id="lbClose">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
  </button>
  <button class="lb-nav lb-prev" id="lbPrev">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
  </button>
  <div class="lb-wrap"><img id="lbImg" src="" alt=""></div>
  <button class="lb-nav lb-next" id="lbNext">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  </button>
  <div class="lb-counter" id="lbCounter"></div>
</div>

<script>
  const allItems = [...document.querySelectorAll('.g-item[data-src]')];
  const lb       = document.getElementById('lightbox');
  const lbImg    = document.getElementById('lbImg');
  const lbCnt    = document.getElementById('lbCounter');
  let cur = 0;

  /* ── Show More ── */
  const showMoreBtn = document.getElementById('showMoreBtn');
  if (showMoreBtn) {
    showMoreBtn.addEventListener('click', () => {
      const isOpen = showMoreBtn.classList.contains('open');
      document.querySelectorAll('.g-item.hidden').forEach(el => {
        if (!isOpen) {
          el.classList.add('revealed');
          el.style.animation = 'fadeIn .35s ease both';
        } else {
          el.classList.remove('revealed');
        }
      });
      showMoreBtn.classList.toggle('open');
      const extra = <?= $total - $limit ?>;
      showMoreBtn.innerHTML = isOpen
        ? `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg> ดูรูปทั้งหมด (${extra} รูปที่เหลือ)`
        : `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 15l-6-6-6 6"/></svg> ซ่อนรูปที่เหลือ`;
    });
  }

  /* ── Lightbox: only visible items clickable ── */
  function getVisibleItems() {
    return allItems.filter(el => !el.classList.contains('hidden') || el.classList.contains('revealed'));
  }

  allItems.forEach((item) => {
    item.addEventListener('click', () => {
      const visible = getVisibleItems();
      const idx = visible.indexOf(item);
      if (idx === -1) return;
      cur = idx;
      openLb(visible);
    });
  });

  let visGroup = [];

  function openLb(group) {
    visGroup = group;
    showLb();
    lb.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function showLb() {
    lbImg.style.opacity = '0';
    setTimeout(() => {
      lbImg.src = visGroup[cur].dataset.src;
      lbImg.style.opacity = '1';
      lbImg.style.transition = 'opacity .22s';
    }, 100);
    lbCnt.textContent = (cur + 1) + ' / ' + visGroup.length;
  }

  function closeLb() { lb.classList.remove('open'); document.body.style.overflow = '' }

  document.getElementById('lbClose').addEventListener('click', closeLb);
  document.getElementById('lbPrev').addEventListener('click', () => { cur = (cur - 1 + visGroup.length) % visGroup.length; showLb() });
  document.getElementById('lbNext').addEventListener('click', () => { cur = (cur + 1) % visGroup.length; showLb() });
  lb.addEventListener('click', e => { if (e.target === lb) closeLb() });

  document.addEventListener('keydown', e => {
    if (!lb.classList.contains('open')) return;
    if (e.key === 'Escape')      closeLb();
    if (e.key === 'ArrowLeft')  { cur = (cur - 1 + visGroup.length) % visGroup.length; showLb() }
    if (e.key === 'ArrowRight') { cur = (cur + 1) % visGroup.length; showLb() }
  });

  /* fade-in animation for revealed items */
  const style = document.createElement('style');
  style.textContent = '@keyframes fadeIn { from{opacity:0;transform:scale(.95)} to{opacity:1;transform:none} }';
  document.head.appendChild(style);
</script>

<?php require_once __DIR__.'/includes/footer.php'; ?>
</body>
</html>