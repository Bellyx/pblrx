<?php
require_once 'config/db.php';
require_once 'includes/header.php';

$cats = $pdo->query("
  SELECT * FROM showcase_categories
  WHERE is_active = 1
  ORDER BY sort_order
")->fetchAll();

$allImgs = [];
foreach ($cats as $c) {
    $stmt = $pdo->prepare("SELECT * FROM showcase_images WHERE category_id = ? ORDER BY id ASC");
    $stmt->execute([$c['id']]);
    $allImgs[$c['id']] = $stmt->fetchAll();
}

// helper: extract YouTube video ID
function ytId($url) {
    preg_match('/(?:v=|youtu\.be\/|embed\/)([^&\s?\/]+)/', $url ?? '', $m);
    return $m[1] ?? '';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Showcase – PBL-R Thailand</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Noto+Sans+Thai:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="/PBLR/assets/css/fontend/styleshowcase.css" rel="stylesheet">
</head>
<body>

<!-- Hero -->
<section class="showcase-hero">
  <div class="container">
    <div class="hero-eyebrow"><span class="dot"></span> Portfolio</div>
    <h1>ผลงาน &amp; <em>Showcase</em></h1>
    <p>รวมผลงานโครงการวิจัยและบริการของ PBL-R Thailand</p>
    <div class="gold-rule"></div>
  </div>
</section>

<!-- Filter Dropdown -->
<?php if (count($cats) > 1): ?>
<div class="filter-wrap">
  <span class="filter-label">หมวดหมู่</span>
  <div class="filter-dropdown" id="filterDropdown">
    <div class="filter-selected" id="filterSelected">
      <span id="filterSelectedText">ทั้งหมด</span>
      <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
    </div>
    <div class="filter-menu" id="filterMenu">
      <div class="filter-option active" data-cat="all"><span class="opt-dot"></span> ทั้งหมด</div>
      <?php foreach ($cats as $c): ?>
        <div class="filter-option" data-cat="cat-<?= $c['id'] ?>">
          <span class="opt-dot"></span><?= htmlspecialchars($c['title_th']) ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Body -->
<div class="showcase-body">
<?php foreach ($cats as $c):
  $allMedia = $allImgs[$c['id']] ?? [];
  if (empty($allMedia)) continue;

  // split images vs videos
  $images = array_values(array_filter($allMedia, fn($m) => ($m['media_type'] ?? 'image') === 'image'));
  $videos = array_values(array_filter($allMedia, fn($m) => ($m['media_type'] ?? 'image') === 'video'));

  $catId = 'cat-'.$c['id'];
?>
<section class="cat-section" data-cat="<?= $catId ?>">

  <!-- Category header -->
  <div class="cat-header">
    <div>
      <div class="cat-label-th"><?= htmlspecialchars($c['title_th']) ?></div>
      <?php if (!empty($c['title_en'])): ?>
        <div class="cat-label-en"><?= htmlspecialchars($c['title_en']) ?></div>
      <?php endif; ?>
    </div>
    <span class="cat-count"><?= count($allMedia) ?> รายการ</span>
    <div class="cat-line"></div>
  </div>

  <?php /* ══ IMAGE GRID ══ */ if (!empty($images)): ?>
  <?php if (!empty($videos)): ?>
    <div class="sub-label">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
      รูปภาพ
    </div>
  <?php endif; ?>
  <div class="img-grid">
    <?php foreach ($images as $idx => $i): ?>
      <div class="grid-item"
        data-src="assets/upload/showcase/<?= htmlspecialchars($i['image']) ?>"
        data-group="<?= $catId ?>-img"
        data-index="<?= $idx ?>">
        <img src="assets/upload/showcase/<?= htmlspecialchars($i['image']) ?>" alt="showcase" loading="lazy">
        <div class="grid-overlay">
          <div class="grid-overlay-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php /* ══ VIDEO PLAYLIST ══ */ if (!empty($videos)):
    $firstVid = ytId($videos[0]['youtube_url']);
  ?>
  <?php if (!empty($images)): ?>
    <div class="sub-label" style="margin-top:.5rem">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
      วิดีโอ
    </div>
  <?php endif; ?>

  <div class="vp-wrap" id="vp-<?= $c['id'] ?>">
    <div class="vp-inner">

      <!-- Main Player -->
      <div class="vp-player">
        <iframe
          id="vp-frame-<?= $c['id'] ?>"
          src="https://www.youtube.com/embed/<?= htmlspecialchars($firstVid) ?>?rel=0&modestbranding=1"
          allowfullscreen
          allow="autoplay; encrypted-media">
        </iframe>
      </div>

      <!-- Playlist Sidebar -->
      <div class="vp-list">
        <div class="vp-list-head">
          <span class="vp-list-title">Playlist</span>
          <span class="vp-list-count"><?= count($videos) ?> Videos</span>
        </div>

        <div class="vp-list-items">
          <?php foreach ($videos as $vi => $v):
            $vid = ytId($v['youtube_url']);
            if (!$vid) continue;
          ?>
            <div class="vp-item <?= $vi === 0 ? 'active' : '' ?>"
              data-vid="<?= htmlspecialchars($vid) ?>"
              data-cat-id="<?= $c['id'] ?>"
              onclick="playVideo(this)">
              <div class="vp-thumb-wrap">
                <img src="https://img.youtube.com/vi/<?= htmlspecialchars($vid) ?>/mqdefault.jpg" alt="" loading="lazy">
                <div class="vp-play-icon">
                  <svg viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                </div>
              </div>
              <div class="vp-item-info">
                <div class="vp-item-title">วิดีโอที่ <?= $vi + 1 ?></div>
                <div class="vp-item-num">#<?= $vi + 1 ?> / <?= count($videos) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Now playing bar -->
        <div class="vp-now">
          <span class="vp-now-dot"></span>
          <span class="vp-now-text" id="vp-now-<?= $c['id'] ?>">กำลังเล่น: วิดีโอที่ 1</span>
        </div>
      </div>

    </div><!-- /.vp-inner -->
  </div><!-- /.vp-wrap -->
  <?php endif; ?>

</section>
<?php endforeach; ?>
</div>

<!-- Lightbox -->
<div class="lightbox" id="lightbox">
  <button class="lb-close" id="lbClose">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
  </button>
  <button class="lb-nav lb-prev" id="lbPrev">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
  </button>
  <div class="lb-img-wrap"><img id="lbImg" src="" alt=""></div>
  <button class="lb-nav lb-next" id="lbNext">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  </button>
  <div class="lb-counter" id="lbCounter"></div>
</div>

<script>
/* ── Filter ── */
const selEl   = document.getElementById('filterSelected');
const menuEl  = document.getElementById('filterMenu');
const selText = document.getElementById('filterSelectedText');
const opts    = document.querySelectorAll('.filter-option');
const secs    = document.querySelectorAll('.cat-section');

if (selEl) {
  selEl.addEventListener('click', () => { selEl.classList.toggle('open'); menuEl.classList.toggle('open'); });
  document.addEventListener('click', e => {
    if (!document.getElementById('filterDropdown').contains(e.target)) { selEl.classList.remove('open'); menuEl.classList.remove('open'); }
  });
  opts.forEach(o => o.addEventListener('click', () => {
    opts.forEach(x => x.classList.remove('active'));
    o.classList.add('active');
    selText.textContent = o.textContent.trim();
    selEl.classList.remove('open'); menuEl.classList.remove('open');
    const t = o.dataset.cat;
    secs.forEach(s => s.style.display = (t === 'all' || s.dataset.cat === t) ? '' : 'none');
  }));
}

/* ── Video Playlist ── */
function playVideo(el) {
  const catId = el.dataset.catId;
  const vid   = el.dataset.vid;
  const frame = document.getElementById('vp-frame-' + catId);
  const nowEl = document.getElementById('vp-now-' + catId);

  // update iframe
  frame.src = `https://www.youtube.com/embed/${vid}?rel=0&modestbranding=1&autoplay=1`;

  // update active state
  document.querySelectorAll(`.vp-item[data-cat-id="${catId}"]`).forEach(i => i.classList.remove('active'));
  el.classList.add('active');

  // update now playing text
  const num = el.querySelector('.vp-item-num').textContent.replace('#','').split('/')[0].trim();
  nowEl.textContent = `กำลังเล่น: วิดีโอที่ ${num}`;

  // scroll item into view inside list
  el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/* ── Lightbox ── */
const lb = document.getElementById('lightbox');
const lbImg = document.getElementById('lbImg');
const lbCounter = document.getElementById('lbCounter');
let curGroup = [], curIdx = 0;

function openLb(items, idx) { curGroup = items; curIdx = idx; showLb(); lb.classList.add('open'); document.body.style.overflow = 'hidden'; }
function showLb() {
  lbImg.style.opacity = '0';
  setTimeout(() => { lbImg.src = curGroup[curIdx].dataset.src; lbImg.style.opacity = '1'; lbImg.style.transition = 'opacity .25s'; }, 120);
  lbCounter.textContent = (curIdx + 1) + ' / ' + curGroup.length;
}
function closeLb() { lb.classList.remove('open'); document.body.style.overflow = ''; }

document.querySelectorAll('.grid-item').forEach(item => {
  item.addEventListener('click', () => {
    const g = item.dataset.group;
    const gi = [...document.querySelectorAll(`.grid-item[data-group="${g}"]`)];
    openLb(gi, gi.indexOf(item));
  });
});

document.getElementById('lbClose').addEventListener('click', closeLb);
document.getElementById('lbPrev').addEventListener('click', () => { curIdx = (curIdx - 1 + curGroup.length) % curGroup.length; showLb(); });
document.getElementById('lbNext').addEventListener('click', () => { curIdx = (curIdx + 1) % curGroup.length; showLb(); });
lb.addEventListener('click', e => { if (e.target === lb) closeLb(); });
document.addEventListener('keydown', e => {
  if (!lb.classList.contains('open')) return;
  if (e.key === 'Escape') closeLb();
  if (e.key === 'ArrowLeft') { curIdx = (curIdx - 1 + curGroup.length) % curGroup.length; showLb(); }
  if (e.key === 'ArrowRight') { curIdx = (curIdx + 1) % curGroup.length; showLb(); }
});
</script>

<?php require_once 'includes/footer.php'; ?>
</body>
</html>