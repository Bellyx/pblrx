<?php
require_once '../config/db.php';
require_once '../includes/header.php';

$slug = $_GET['slug'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM services WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$service = $stmt->fetch();

if (!$service) {
    die("Service not found");
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($service['title_th']) ?> – PBL-R Thailand</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Noto+Sans+Thai:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --night:       #0d0f1a;
      --night-2:     #141628;
      --surface:     #f8f7f4;
      --white:       #ffffff;
      --gold:        #c8a94a;
      --gold-pale:   #e6d49a;
      --gold-glow:   rgba(200,169,74,0.13);
      --gold-border: rgba(200,169,74,0.25);
      --ink:         #1a1c2e;
      --ink-mid:     #4a4c62;
      --ink-soft:    rgba(26,28,46,0.45);
      --teal:        #1b7fa8;
      --radius:      16px;
      --radius-sm:   10px;
      --shadow-sm:   0 2px 16px rgba(13,15,26,0.07);
      --shadow-md:   0 10px 40px rgba(13,15,26,0.12);
      --ease:        cubic-bezier(0.4,0,0.2,1);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Noto Sans Thai', sans-serif;
      background: var(--surface);
      color: var(--ink);
    }

    /* ── Hero Banner ── */
    .service-hero {
      background: var(--night);
      position: relative;
      overflow: hidden;
      padding: 5rem 0 4rem;
    }

    .service-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(ellipse 80% 80% at 80% 120%, rgba(200,169,74,0.13) 0%, transparent 65%),
        radial-gradient(ellipse 50% 60% at 10% 10%, rgba(27,127,168,0.08) 0%, transparent 55%);
    }

    .service-hero::after {
      content: '';
      position: absolute;
      inset: 0;
      background-image: radial-gradient(rgba(255,255,255,0.04) 1px, transparent 1px);
      background-size: 30px 30px;
    }

    .service-hero .container {
      position: relative;
      z-index: 2;
      max-width: 860px;
      margin: 0 auto;
      padding: 0 2rem;
    }

    .hero-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: rgba(200,169,74,0.1);
      border: 1px solid var(--gold-border);
      color: var(--gold-pale);
      font-size: 0.7rem;
      font-weight: 600;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      padding: 0.3rem 0.9rem;
      border-radius: 100px;
      margin-bottom: 1.5rem;
      text-decoration: none;
      transition: background 0.2s;
    }

    .hero-eyebrow:hover { background: rgba(200,169,74,0.18); }
    .hero-eyebrow svg { width: 12px; height: 12px; }

    .service-hero h1 {
      font-family: 'DM Serif Display', serif;
      font-size: clamp(2rem, 5vw, 3.4rem);
      color: #fff;
      line-height: 1.2;
      margin-bottom: 1.25rem;
      font-weight: 400;
    }

    .hero-meta {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      flex-wrap: wrap;
    }

    .meta-chip {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      font-size: 0.78rem;
      color: rgba(255,255,255,0.4);
      font-weight: 300;
    }

    .meta-chip svg { width: 13px; height: 13px; opacity: 0.6; }

    /* Gold divider line */
    .gold-rule {
      width: 48px; height: 2px;
      background: linear-gradient(90deg, var(--gold), transparent);
      border-radius: 1px;
      margin: 1.75rem 0 0;
    }

    /* ── Main Layout ── */
    .service-layout {
      max-width: 860px;
      margin: 0 auto;
      padding: 3.5rem 2rem 6rem;
    }

    /* ── Image ── */
    .service-image-wrap {
      position: relative;
      margin-bottom: 3rem;
      border-radius: var(--radius);
      overflow: hidden;
      box-shadow: var(--shadow-md);
    }

    .service-image-wrap img {
      width: 100%;
      height: auto;
      max-height: 480px;
      object-fit: cover;
      display: block;
    }

    /* Gold bottom bar on image */
    .service-image-wrap::after {
      content: '';
      position: absolute;
      bottom: 0; left: 0; right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--gold), transparent 70%);
    }

    /* ── Content body ── */
    .service-content {
      background: var(--white);
      border-radius: var(--radius);
      border: 1px solid rgba(26,28,46,0.07);
      box-shadow: var(--shadow-sm);
      padding: 2.5rem 3rem;
      margin-bottom: 2rem;
      font-size: 1.02rem;
      color: var(--ink-mid);
      line-height: 1.9;
      font-weight: 300;
      position: relative;
      overflow: hidden;
    }

    /* Left gold accent */
    .service-content::before {
      content: '';
      position: absolute;
      top: 0; left: 0;
      width: 4px; height: 100%;
      background: linear-gradient(to bottom, var(--gold), transparent);
      border-radius: 0;
    }

    .service-content h2,
    .service-content h3 {
      font-family: 'DM Serif Display', serif;
      color: var(--ink);
      margin: 1.5rem 0 0.75rem;
    }

    .service-content p { margin-bottom: 1rem; }
    .service-content ul, .service-content ol {
      padding-left: 1.5rem;
      margin-bottom: 1rem;
    }
    .service-content li { margin-bottom: 0.4rem; }
    .service-content strong { color: var(--ink); font-weight: 600; }
    .service-content a { color: var(--teal); text-underline-offset: 3px; }

    /* ── Video ── */
    .video-wrap {
      border-radius: var(--radius);
      overflow: hidden;
      box-shadow: var(--shadow-md);
      margin-bottom: 2rem;
      background: var(--night);
      position: relative;
      aspect-ratio: 16/9;
    }

    .video-wrap::before {
      content: '';
      position: absolute;
      inset: 0;
      border: 1px solid var(--gold-border);
      border-radius: var(--radius);
      pointer-events: none;
      z-index: 1;
    }

    .video-wrap iframe {
      width: 100%;
      height: 100%;
      border: none;
      display: block;
    }

    /* ── CTA / Link ── */
    .service-cta {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 1rem;
      background: var(--night);
      border-radius: var(--radius);
      border: 1px solid var(--gold-border);
      padding: 2rem 2.5rem;
      margin-bottom: 2rem;
    }

    .cta-text h4 {
      font-family: 'DM Serif Display', serif;
      color: #fff;
      font-size: 1.15rem;
      margin-bottom: 0.3rem;
    }

    .cta-text p {
      font-size: 0.82rem;
      color: rgba(255,255,255,0.4);
      font-weight: 300;
      margin: 0;
    }

    .cta-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.6rem;
      background: var(--gold);
      color: var(--night);
      font-family: 'Noto Sans Thai', sans-serif;
      font-weight: 700;
      font-size: 0.9rem;
      padding: 0.75rem 1.75rem;
      border-radius: 100px;
      text-decoration: none;
      white-space: nowrap;
      box-shadow: 0 4px 20px rgba(200,169,74,0.3);
      transition: background 0.25s, transform 0.25s, box-shadow 0.25s;
    }

    .cta-btn:hover {
      background: var(--gold-pale);
      transform: translateY(-2px);
      box-shadow: 0 8px 28px rgba(200,169,74,0.4);
    }

    .cta-btn svg { width: 15px; height: 15px; }

    /* ── Back link ── */
    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.82rem;
      color: var(--ink-soft);
      text-decoration: none;
      font-weight: 500;
      transition: color 0.2s, gap 0.2s;
      margin-bottom: 2.5rem;
    }

    .back-link:hover { color: var(--ink); gap: 0.75rem; }
    .back-link svg { width: 14px; height: 14px; }

    /* ── Responsive ── */
    @media (max-width: 640px) {
      .service-content { padding: 1.75rem 1.5rem; }
      .service-cta { flex-direction: column; align-items: flex-start; padding: 1.5rem; }
      .service-hero { padding: 3.5rem 0 2.5rem; }
    }
  </style>
</head>
<body>

<!-- ── Hero ── -->
<section class="service-hero">
  <div class="container">
    <a href="index.php" class="hero-eyebrow">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
      กลับหน้าหลัก
    </a>
    <h1><?= htmlspecialchars($service['title_th']) ?></h1>
    <div class="hero-meta">
      <span class="meta-chip">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        <?= date('d M Y', strtotime($service['created_at'])) ?>
      </span>
      <?php if (!empty($service['category'])): ?>
      <span class="meta-chip">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
        <?= htmlspecialchars($service['category']) ?>
      </span>
      <?php endif; ?>
    </div>
    <div class="gold-rule"></div>
  </div>
</section>


<!-- ── Content ── -->
<div class="service-layout">



  <!-- Content body -->
  <?php if (!empty($service['content_th'])): ?>
    <div class="service-content">
      <?= $service['content_th'] ?>
    </div>
  <?php endif; ?>
  
  <!-- Image -->
  <?php if (!empty($service['image'])): ?>
    <div class="service-image-wrap">
      <img
        src="../assets/upload/services/<?= htmlspecialchars($service['image']) ?>"
        alt="<?= htmlspecialchars($service['title_th']) ?>"
      >
    </div>
  <?php endif; ?>
  <!-- Video -->
  <?php if (!empty($service['video_url'])): ?>
    <div class="video-wrap">
      <iframe
        src="<?= htmlspecialchars($service['video_url']) ?>"
        allowfullscreen
        loading="lazy"
      ></iframe>
    </div>
  <?php endif; ?>

  <!-- External link CTA -->
  <?php if (!empty($service['link_url'])): ?>
    <div class="service-cta">
      <div class="cta-text">
        <h4>ต้องการข้อมูลเพิ่มเติม?</h4>
        <p>คลิกเพื่อเยี่ยมชมเว็บไซต์ที่เกี่ยวข้อง</p>
      </div>
      <a href="<?= htmlspecialchars($service['link_url']) ?>" target="_blank" rel="noopener" class="cta-btn">
        Visit Website
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      </a>
    </div>
  <?php endif; ?>

</div>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>