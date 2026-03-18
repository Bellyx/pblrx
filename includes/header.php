<?php
require_once __DIR__ . "/lang.php";
require_once __DIR__ . "/../config/db.php";

$lang = $_SESSION["lang"] ?? "th";
$page = basename($_SERVER["PHP_SELF"]);

function active($p, $page)
{
    return $p === $page ? "active" : "";
}

/* ดึง services สำหรับ dropdown */

$stmt = $pdo->prepare("
SELECT slug, title_th, title_en
FROM services
WHERE is_active = 1
ORDER BY sort_order ASC
");

$stmt->execute();
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="
<?= $lang ?>">

<head>
    <meta charset="utf-8">
    <title>PBL-R Thailand</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- CSS -->
    <link rel="stylesheet" href="/PBLR/assets/css/stylenavbar.css">
    <link rel="stylesheet" href="/PBLR/assets/css/style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg sticky-top" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand" href="/PBLR/index.php">PBL-R</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto gap-lg-4">
                    <li class="nav-item">
                        <a class="nav-link 
									<?= active('index.php', $page) ?>" href="/PBLR/index.php"> <?= $lang === 'th' ? 'หน้าแรก' : 'Home' ?> </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link 
									<?= active('about.php', $page) ?>" href="/PBLR/about.php"> <?= $lang === 'th' ? 'เกี่ยวกับ' : 'About' ?> </a>
                    </li>
                    <!-- SERVICES DROPDOWN -->
                    <li class="nav-item dropdown service-dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <?= $lang === 'th' ? 'บริการ' : 'Services' ?> </a>
                        <ul class="dropdown-menu service-menu"> <?php foreach ($services as $s): ?> <li>
                                <a class="dropdown-item service-item" href="/PBLR/services/
											<?= urlencode($s['slug']) ?>">
                                    <span class="dot"></span> <?= htmlspecialchars($lang === 'th'
                                                                        ? $s['title_th']
                                                                        : $s['title_en']) ?> </a>
                            </li> <?php endforeach ?> </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link 
									<?= active('activities.php', $page) ?>" href="/PBLR/activities.php">
                            <?= $lang === 'th' ? 'กิจกรรม' : 'Activities' ?> </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link 
									<?= active('showcase.php', $page) ?>" href="/PBLR/showcase.php">
                            <?= $lang === 'th' ? 'ผลงาน' : 'work' ?> </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link 
									<?= active('contact.php', $page) ?>" href="/PBLR/contact.php"> <?= $lang === 'th' ? 'ติดต่อ' : 'Contact' ?>
                        </a>
                    </li>
                </ul>
                <!-- LANGUAGE SWITCH -->
                <div class="ms-lg-4 lang-group">
                    <a href="/PBLR/lang.php?lang=th" class="lang-btn 
								<?= $lang === 'th' ? 'active' : '' ?>"> TH </a>
                    <a href="/PBLR/lang.php?lang=en" class="lang-btn 
								<?= $lang === 'en' ? 'active' : '' ?>"> EN </a>
                </div>
            </div>
        </div>
    </nav>
    <script>
    const nav = document.getElementById('mainNavbar')
    window.addEventListener('scroll', () => {
        nav.classList.toggle('scrolled', window.scrollY > 40)
    }, {
        passive: true
    })
    </script>