<?php
include 'config/db.php';
include __DIR__ . '/includes/header.php';
?>

<?php
// เครือข่ายในประเทศ
$stmts = $pdo->prepare("
  SELECT country_name_th, country_name_en, flag_url
  FROM country_network
  WHERE status=1 AND network_type='TH'
  ORDER BY sort_order ASC
");
$stmts->execute();
$domesticCountries = $stmts->fetchAll(PDO::FETCH_ASSOC);

// เครือข่ายต่างประเทศ
$internationalStmt = $pdo->prepare("
  SELECT country_name_th, country_name_en, flag_url
  FROM country_network
  WHERE status = 1 AND network_type = 'INT'
  ORDER BY sort_order ASC
");
$internationalStmt->execute();
$internationalCountries = $internationalStmt->fetchAll(PDO::FETCH_ASSOC);

// บุคลากร
$personnelStmt = $pdo->prepare("
  SELECT name, position, image
  FROM personnel
  WHERE status = 1
  ORDER BY sort_order ASC
");
$personnelStmt->execute();
$personnels = $personnelStmt->fetchAll(PDO::FETCH_ASSOC);

// ข้อมูลเกี่ยวกับ
$about = $pdo->query("
  SELECT * FROM about_content WHERE id = 1
")->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>เกี่ยวกับเรา – PBL-R Thailand</title>
  <link href="/PBLR/assets/css/styleab.css" rel="stylesheet">
</head>

<body>

  <!-- ══════════════════════════════════════
     HERO
══════════════════════════════════════ -->
  <section class="about-hero">
    <div class="container">
      <span class="badge">About Us</span>
      <h1>กว่าจะเป็น <span>PBL-R Thailand</span></h1>
      <p>
        <?= nl2br(htmlspecialchars($about['story_short'] ?? 'ศูนย์วิจัยและพัฒนานโยบายเพื่อสังคมอย่างยั่งยืน')) ?>
      </p>
    </div>
    <div class="scroll-hint">
      <span>scroll</span>
      <div class="arrow"></div>
    </div>
  </section>


  <!-- ══════════════════════════════════════
     STORY
══════════════════════════════════════ -->
  <section class="about-story">
    <div class="container">
      <div class="story-box reveal">
        <?= $about['story'] ?>
      </div>
    </div>
  </section>


  <!-- ══════════════════════════════════════
     VISION / MISSION
══════════════════════════════════════ -->
  <section class="vision-mission">
    <div class="container">
      <div class="row g-4">

        <div class="col-lg-6">
          <div class="vm-card reveal">
            <div class="vm-icon mb-3">👁️</div>
            <h4 class="mb-3">วิสัยทัศน์</h4>
            <div class="vm-content">
              <?= $about['vision'] ?>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="vm-card reveal">
            <div class="vm-icon mb-3">🎯</div>
            <h4 class="mb-3">พันธกิจ</h4>
            <div class="vm-content">
              <?= $about['mission'] ?>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>


  <!-- ══════════════════════════════════════
     NETWORK
══════════════════════════════════════ -->
  <section class="network-section">
    <div class="container">

      <h2 class="network-title">เครือข่ายความร่วมมือ</h2>
      <p class="network-sub">พันธมิตรด้านงานวิจัยและการพัฒนา ทั้งในประเทศและต่างประเทศ</p>

      <!-- เครือข่ายในประเทศ -->
      <div class="network-block reveal">
        <h4 class="network-group">เครือข่ายภายในประเทศ</h4>
        <div class="network-grid">
          <?php foreach ($domesticCountries as $c): ?>

            <?php
            $flag = $c['flag_url'];

            if (!preg_match('/^https?:\/\//', $flag)) {
              $flag = '/PBLR/assets/upload/flags/' . basename($flag);
            }
            ?>

            <div class="network-card">

              <img
                src="<?= htmlspecialchars($flag) ?>"
                alt="<?= htmlspecialchars($c['country_name_th']) ?>"
                loading="lazy">

              <span><?= htmlspecialchars($c['country_name_th']) ?></span>

            </div>

          <?php endforeach; ?>
        </div>
      </div>

      <!-- เครือข่ายต่างประเทศ -->
      <div class="network-block reveal">
        <h4 class="network-group">เครือข่ายต่างประเทศ</h4>
        <div class="network-grid">
          <?php foreach ($internationalCountries as $c): ?>

            <?php
            $flag = $c['flag_url'];

            if (!preg_match('/^https?:\/\//', $flag)) {
              $flag = '/PBLR/assets/upload/flags/' . basename($flag);
            }
            ?>

            <div class="network-card">

              <img
                src="<?= htmlspecialchars($flag) ?>"
                alt="<?= htmlspecialchars($c['country_name_th']) ?>"
                loading="lazy">

              <span><?= htmlspecialchars($c['country_name_th']) ?></span>

            </div>

          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </section>


  <!-- ══════════════════════════════════════
     PERSONNEL
══════════════════════════════════════ -->
  <section class="personnel-section">
    <div class="container">

      <h2>ทำเนียบบุคลากร</h2>
      <p class="subtitle">ทีมนักวิจัยและผู้เชี่ยวชาญของเรา</p>

      <div class="row g-4 justify-content-center">
        <?php foreach ($personnels as $row): ?>
          <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="personnel-card reveal">
              <img
                src="assets/upload/personnel/<?= htmlspecialchars($row['image'] ?: 'no-image.png') ?>"
                alt="<?= htmlspecialchars($row['name']) ?>"
                loading="lazy">
              <h5><?= htmlspecialchars($row['name']) ?></h5>
              <p><?= htmlspecialchars($row['position']) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    </div>
  </section>


  <!-- ══════════════════════════════════════
     SCROLL REVEAL SCRIPT
══════════════════════════════════════ -->
  <script>
    const revealEls = document.querySelectorAll('.reveal');
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry, i) => {
        if (entry.isIntersecting) {
          setTimeout(() => entry.target.classList.add('visible'), i * 80);
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.12
    });
    revealEls.forEach(el => observer.observe(el));
  </script>

</body>

</html>

<?php require_once __DIR__ . '/includes/footer.php'; ?>