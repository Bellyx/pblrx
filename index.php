<?php
include 'config/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/services_cache.php';

$stmt = $pdo->query("SELECT * FROM services ORDER BY id ASC");
$services = $stmt->fetchAll();
$lang = $_SESSION['lang'] ?? 'th';
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PBL-R Thailand – ศูนย์วิจัยและพัฒนา</title>
  <link href="/PBLR/assets/css/fontend/styleindex.css" rel="stylesheet">
</head>
<body>

<!-- ══════════════════════════════════════
     HERO
══════════════════════════════════════ -->
<section class="hero">
  <div class="accent-line"></div>
  <div class="container">
    <span class="hero-eyebrow">
      <span class="dot"></span>
      Problem-based Learning Research
    </span>
    <h1>
      <?= $T['hero_title'] ?? 'วิจัยเพื่อ<em>สังคมที่ยั่งยืน</em>' ?>
    </h1>
    <p><?= $T['hero_sub'] ?? 'ทีมนักวิจัยผู้เชี่ยวชาญหลากสาขา พร้อมขับเคลื่อนงานวิจัยคุณภาพสูงเพื่อการพัฒนาที่แท้จริง' ?></p>
    <div class="hero-actions">
      <a href="contact.php" class="btn-primary-gold">
        <?= $T['contact'] ?? 'ติดต่อเรา' ?>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <a href="about.php" class="btn-outline-white">
        เกี่ยวกับองค์กร
      </a>
    </div>
  </div>

  <!-- Floating stat bar -->
  <div class="hero-stats">
    <div class="hero-stats-inner">
      <div class="hero-stat">
        <div class="num">10+</div>
        <div class="label">Years Experience</div>
      </div>
      <div class="hero-stat">
        <div class="num">100+</div>
        <div class="label">Projects Completed</div>
      </div>
      <div class="hero-stat">
        <div class="num">ISO</div>
        <div class="label">Standard Quality</div>
      </div>
      <div class="hero-stat">
        <div class="num">Pro</div>
        <div class="label">Research Team</div>
      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════
     ABOUT
══════════════════════════════════════ -->
<section class="about-section">
  <div class="container">
    <div class="row">

      <div class="about-text sr sr--left">
        <span class="section-tag">เกี่ยวกับเรา</span>
        <h2>
          <?= $T['about'] ?? 'วิจัยเพื่อ<span>สังคม</span>' ?>
        </h2>
        <p>
          <strong>Problem-based Learning Research</strong>
          กลุ่มนักวิจัยผู้เชี่ยวชาญทางด้านเศรษฐศาสตร์การเกษตร
          ด้านเทคโนโลยีการศึกษา ด้านการพัฒนาองค์กร
          ด้าน Information And Communication Technology (ICT)
          ด้าน Startup และด้านงานวิจัยองค์รวม
        </p>
        <p>
          พวกเรารวมตัวกันสำหรับภารกิจงานวิจัยเพื่อพัฒนาสังคม
          ทีมวิจัยของเรารับจัดทำงานและบริการด้านวิจัยอย่างมีคุณภาพ
        </p>
        <a href="about.php" class="about-cta">
          อ่านเพิ่มเติม
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      </div>

      <div class="about-image sr sr--right">
        <div class="about-image-wrap">
          <img src="./assets/images/testlogo.png" alt="PBL-R Thailand">
        </div>
      </div>

    </div>
  </div>
</section>


<!-- ══════════════════════════════════════
     SERVICES
══════════════════════════════════════ -->
<section class="services-section">
  <div class="container">

    <div class="services-header sr">
      <h3><?= $T['services'] ?? 'บริการของเรา' ?></h3>
      <div class="gold-divider"><span></span><em></em><span></span></div>
      <p>บริการวิจัยและพัฒนาครอบคลุมทุกมิติ ด้วยทีมผู้เชี่ยวชาญเฉพาะสาขา</p>
    </div>

    <div class="services-box sr">
      <div class="services-grid">
        <?php foreach ($services as $s): ?>
          <div class="service-item">
            <div class="icon-circle">
              <?= file_get_contents(__DIR__ . '/assets/icons/' . $s['icon']); ?>
            </div>
            <h6><?= htmlspecialchars($lang === 'th' ? $s['title_th'] : $s['title_en']) ?></h6>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</section>


<!-- ══════════════════════════════════════
     STATS
══════════════════════════════════════ -->
<section class="stats-section">
  <div class="container">
    <div class="stats-grid">
      <div class="stat-item sr sr-delay-1">
        <h3>10+</h3>
        <p>Years Experience</p>
      </div>
      <div class="stat-item sr sr-delay-2">
        <h3>100+</h3>
        <p>Projects Completed</p>
      </div>
      <div class="stat-item sr sr-delay-3">
        <h3>ISO</h3>
        <p>Standard Quality</p>
      </div>
      <div class="stat-item sr sr-delay-4">
        <h3>Pro</h3>
        <p>Research Team</p>
      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════
     ACTIVITIES
══════════════════════════════════════ -->
<?php
$stmt = $pdo->query("SELECT * FROM activities ORDER BY id DESC LIMIT 4");
?>
<section class="activities-section">
  <div class="container">

    <div class="section-heading sr">
      <h2><?= $T['activities'] ?? 'กิจกรรมของเรา' ?></h2>
      <div class="gold-divider"><span></span><em></em><span></span></div>
      <p>ติดตามกิจกรรมและโครงการล่าสุดจากทีมนักวิจัย</p>
    </div>

    <div class="activities-grid">
      <?php while ($a = $stmt->fetch()): ?>
        <div class="activity-card sr">
          <div class="img-wrap">
            <img
              src="assets/upload/activities/<?= htmlspecialchars($a['image_path']) ?>"
              alt="<?= htmlspecialchars($a['title_th']) ?>"
              loading="lazy"
            >
          </div>
          <div class="card-body">
            <h6><?= htmlspecialchars($a['title_th']) ?></h6>
            <span class="card-arrow">
              อ่านเพิ่มเติม
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </span>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <div class="activities-footer sr">
      <a href="activities.php" class="btn-outline-ink">
        ดูกิจกรรมทั้งหมด
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>

  </div>
</section>


<!-- ══════════════════════════════════════
     CTA
══════════════════════════════════════ -->
<section class="cta-section">
  <div class="cta-ring"></div>
  <div class="cta-ring cta-ring-2"></div>
  <div class="container">
    <h3 class="sr">พร้อมเริ่มโครงการ<br>กับเราหรือยัง?</h3>
    <p class="sr">ติดต่อเราวันนี้เพื่อปรึกษาโครงการวิจัยของคุณ</p>
    <a href="contact.php" class="btn-primary-gold sr">
      ติดต่อเราเลย
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
  </div>
</section>


<!-- ══════════════════════════════════════
     SCROLL REVEAL
══════════════════════════════════════ -->
<script>
  const els = document.querySelectorAll('.sr');
  const io  = new IntersectionObserver((entries) => {
    entries.forEach((e, i) => {
      if (e.isIntersecting) {
        setTimeout(() => e.target.classList.add('on'), i * 90);
        io.unobserve(e.target);
      }
    });
  }, { threshold: 0.1 });
  els.forEach(el => io.observe(el));
</script>

</body>
</html>

<?php include 'includes/footer.php'; ?>