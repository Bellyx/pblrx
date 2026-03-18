<?php
?>
<!DOCTYPE html>
<html lang="th">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard – PBL-R Backoffice</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<link href="/PBLR/assets/css/stylesidebar.css" rel="stylesheet">

</head>

<body>


<!-- Mobile overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ══════════════════════════════════════
     SIDEBAR
══════════════════════════════════════ -->
<div class="sidebar" id="sidebar">

  <!-- Brand -->
  <div class="logo">
    <div class="logo-icon">
      <i class="fas fa-cube"></i>
    </div>
    <h2 class="logo-text">PBLR Backoffice</h2>
  </div>

  <!-- Menu -->
  <div class="menu-section">

    <div class="menu-label">Main Menu</div>

    <!-- หน้าแรก -->
    <a href="/PBLR/admin/dashboard/ds_index.php" class="menu-item">
      <div class="menu-item-content">
        <span class="menu-icon"><i class="fas fa-chart-line"></i></span>
        <span class="menu-text">หน้าแรก</span>
      </div>
    </a>

    <div class="menu-divider"></div>
    <div class="menu-label">จัดการเนื้อหา</div>

    <!-- เกี่ยวกับเรา -->
    <div class="menu-group">
      <a class="menu-item menu-toggle" role="button">
        <div class="menu-item-content">
          <span class="menu-icon"><i class="fas fa-building"></i></span>
          <span class="menu-text">เกี่ยวกับเรา</span>
        </div>
        <i class="fas fa-chevron-down menu-arrow"></i>
      </a>
      <div class="submenu">
        <a href="/PBLR/admin/dashboard/about/about_cm.php"     class="submenu-item">ข้อมูลบริษัท</a>
        <a href="/PBLR/admin/dashboard/about/about_edit.php"   class="submenu-item">ทีมงาน</a>
        <a href="/PBLR/admin/dashboard/about/about_network.php" class="submenu-item">เครือข่ายความร่วมมือ</a>
      </div>
    </div>

    <!-- บริการ -->
    <div class="menu-group">
      <a class="menu-item menu-toggle" role="button">
        <div class="menu-item-content">
          <span class="menu-icon"><i class="fas fa-briefcase"></i></span>
          <span class="menu-text">บริการ</span>
        </div>
        <i class="fas fa-chevron-down menu-arrow"></i>
      </a>
      <div class="submenu">
        <a href="/PBLR/admin/dashboard/services/services_cms.php" class="submenu-item">รายการบริการ</a>
        <a href="#" class="submenu-item">หมวดหมู่</a>
        <a href="#" class="submenu-item">วิสัยทัศน์</a>
      </div>
    </div>

    <!-- ข่าวสาร -->
    <a href="/PBLR/admin/dashboard/activity/activity_index.php" class="menu-item">
      <div class="menu-item-content">
        <span class="menu-icon"><i class="fas fa-newspaper"></i></span>
        <span class="menu-text">ข่าวสาร</span>
      </div>
    </a>

    <!-- กิจกรรม -->
    <a href="#" class="menu-item">
      <div class="menu-item-content">
        <span class="menu-icon"><i class="fas fa-calendar-alt"></i></span>
        <span class="menu-text">กิจกรรม</span>
      </div>
    </a>

    <!-- กิจกรรม -->
       <a href="/PBLR/admin/dashboard/showcase/showcase_cms.php" class="menu-item">
      <div class="menu-item-content">
        <span class="menu-icon"><i class="fas fa-calendar-alt"></i></span>
        <span class="menu-text">ผลงาน</span>
      </div>
    </a>

    <!-- ติดต่อเรา -->
    <a href="/PBLR/admin/dashboard/contact/index.php" class="menu-item">
      <div class="menu-item-content">
        <span class="menu-icon"><i class="fas fa-newspaper"></i></span>
        <span class="menu-text">ติดต่อ</span>
      </div>
    </a>

    <div class="menu-divider"></div>
    <div class="menu-label">ระบบ</div>

    <!-- ตั้งค่า -->
    <a href="#" class="menu-item">
      <div class="menu-item-content">
        <span class="menu-icon"><i class="fas fa-cog"></i></span>
        <span class="menu-text">ตั้งค่า</span>
      </div>
    </a>

  </div><!-- /.menu-section -->

  <!-- User footer -->
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-avatar">A</div>
      <div class="sidebar-user-info">
        <div class="sidebar-user-name">Admin</div>
        <div class="sidebar-user-role">Super Administrator</div>
      </div>
      <i class="fas fa-sign-out-alt" style="font-size:0.75rem;color:rgba(255,255,255,0.2)"></i>
    </div>
  </div>

</div><!-- /.sidebar -->


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Accordion toggle
  document.querySelectorAll('.menu-toggle').forEach(menu => {
    menu.addEventListener('click', e => {
      e.preventDefault();
      const isOpen = menu.classList.contains('open');

      // Close all
      document.querySelectorAll('.menu-toggle').forEach(m => {
        m.classList.remove('open');
        const sub = m.nextElementSibling;
        if (sub?.classList.contains('submenu')) sub.classList.remove('show');
      });

      // Open clicked (if it wasn't open)
      if (!isOpen) {
        menu.classList.add('open');
        const submenu = menu.nextElementSibling;
        if (submenu?.classList.contains('submenu')) submenu.classList.add('show');
      }
    });
  });

  // Mobile overlay
  const overlay = document.getElementById('sidebarOverlay');
  overlay?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.remove('open');
    overlay.classList.remove('show');
  });
</script>
