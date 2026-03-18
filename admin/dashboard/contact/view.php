<?php
include '../../../config/db.php';
require_once '../../../includes/header_db.php';

$id   = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
$stmt->execute([$id]);
$row  = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
  header("Location: contact_index.php");
  exit;
}

/* Auto mark as read on open */
if (empty($row['is_read'])) {
  $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?")->execute([$id]);
}

/* Prev / Next */
$prev = $pdo->query("SELECT id FROM contact_messages WHERE id < $id ORDER BY id DESC LIMIT 1")->fetch();
$next = $pdo->query("SELECT id FROM contact_messages WHERE id > $id ORDER BY id ASC  LIMIT 1")->fetch();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ดูข้อความ – PBLR Backoffice</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <link href="/PBLR/assets/css/backend/stylecontactview.css" rel="stylesheet">
  <style>
 
  </style>
</head>
<body>

<div class="page">

  <!-- Top bar -->
  <div class="top-bar">
    <div class="top-bar-left">
      <a href="index.php" class="back-btn">
        <i class="bi bi-arrow-left"></i> กลับ
      </a>
      <div class="nav-btns">
        <a href="?id=<?= $prev['id'] ?? '' ?>"
           class="nav-btn <?= $prev ? '' : 'disabled' ?>" title="ข้อความก่อนหน้า">
          <i class="bi bi-chevron-left"></i>
        </a>
        <a href="?id=<?= $next['id'] ?? '' ?>"
           class="nav-btn <?= $next ? '' : 'disabled' ?>" title="ข้อความถัดไป">
          <i class="bi bi-chevron-right"></i>
        </a>
      </div>
    </div>

    <div class="top-actions">
      <a href="mailto:<?= htmlspecialchars($row['email']) ?>?subject=Re: <?= urlencode($row['subject'] ?? '') ?>"
         class="action-btn reply">
        <i class="bi bi-reply"></i> ตอบกลับ
      </a>
      <button class="action-btn delete"
              onclick="confirmDelete(<?= $row['id'] ?>)">
        <i class="bi bi-trash"></i> ลบ
      </button>
    </div>
  </div>

  <!-- Layout -->
  <div class="view-layout">

    <!-- ══ Message ══ -->
    <div class="view-main">
      <div class="card">
        <div class="card-header">
          <i class="bi bi-envelope-open"></i>
          <span>เนื้อหาข้อความ</span>
        </div>
        <div class="card-body">

          <!-- Subject -->
          <div class="msg-subject">
            <?= htmlspecialchars($row['subject'] ?: '(ไม่มีหัวข้อ)') ?>
          </div>

          <!-- Meta row -->
          <div class="msg-meta-row">
            <span class="msg-sender-chip">
              <span class="avatar-circle">
                <?= mb_strtoupper(mb_substr($row['name'], 0, 1)) ?>
              </span>
              <?= htmlspecialchars($row['name']) ?>
            </span>
            <span class="sep">·</span>
            <span><?= htmlspecialchars($row['email']) ?></span>
            <span class="sep">·</span>
            <span>
              <i class="bi bi-clock" style="font-size:.68rem;margin-right:2px"></i>
              <?= date('d M Y, H:i น.', strtotime($row['created_at'])) ?>
            </span>
          </div>

          <!-- Body -->
          <div class="msg-body"><?= htmlspecialchars($row['message']) ?></div>

        </div>
      </div>
    </div>

    <!-- ══ Sidebar ══ -->
    <div class="view-sidebar">

      <!-- Sender info -->
      <div class="card">
        <div class="card-header">
          <i class="bi bi-person"></i>
          <span>ข้อมูลผู้ส่ง</span>
        </div>
        <div class="card-body">
          <div class="meta-item">
            <span class="meta-label">ชื่อ</span>
            <span class="meta-value"><?= htmlspecialchars($row['name']) ?></span>
          </div>
          <div class="meta-item">
            <span class="meta-label">อีเมล</span>
            <span class="meta-value">
              <a href="mailto:<?= htmlspecialchars($row['email']) ?>">
                <?= htmlspecialchars($row['email']) ?>
              </a>
            </span>
          </div>
          <div class="meta-item">
            <span class="meta-label">วันที่ส่ง</span>
            <span class="meta-value"><?= date('d M Y', strtotime($row['created_at'])) ?></span>
          </div>
          <div class="meta-item">
            <span class="meta-label">เวลา</span>
            <span class="meta-value"><?= date('H:i น.', strtotime($row['created_at'])) ?></span>
          </div>
          <div class="meta-item">
            <span class="meta-label">สถานะ</span>
            <span class="meta-value">
              <span class="status-badge read">
                <span class="dot"></span>อ่านแล้ว
              </span>
            </span>
          </div>
        </div>
      </div>

      <!-- Quick reply -->
      <div class="card quick-reply">
        <div class="card-header">
          <i class="bi bi-reply"></i>
          <span>ตอบกลับด่วน</span>
        </div>
        <div class="card-body">
          <textarea class="reply-textarea"
                    id="replyText"
                    placeholder="พิมพ์ข้อความตอบกลับ..."></textarea>
          <a id="replyLink"
             href="mailto:<?= htmlspecialchars($row['email']) ?>?subject=Re: <?= urlencode($row['subject'] ?? '') ?>"
             class="btn-send-reply">
            <i class="bi bi-send"></i> ส่งอีเมล
          </a>
        </div>
      </div>

    </div>

  </div>
</div>

<script>
  /* Append reply text to mailto body */
  document.getElementById('replyText').addEventListener('input', function () {
    const email   = '<?= htmlspecialchars($row['email']) ?>';
    const subject = encodeURIComponent('Re: <?= addslashes($row['subject'] ?? '') ?>');
    const body    = encodeURIComponent(this.value);
    document.getElementById('replyLink').href = `mailto:${email}?subject=${subject}&body=${body}`;
  });

  /* Delete confirm */
  function confirmDelete(id) {
    Swal.fire({
      title: 'ลบข้อความนี้?',
      text: 'ไม่สามารถกู้คืนได้',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#e05c5c',
      cancelButtonColor:  '#4a4c62',
      confirmButtonText:  'ลบเลย',
      cancelButtonText:   'ยกเลิก'
    }).then(r => {
      if (r.isConfirmed) location.href = 'contact_index.php?delete=' + id;
    });
  }
</script>
</body>
</html>