<?php require_once __DIR__ . '/includes/header.php'; ?>
<link rel="stylesheet" href="/PBLR/assets/css/fontend/stylecontact.css">

<!-- Hero -->
<section class="contact-hero">
  <div class="container">
    <span class="eyebrow">Contact Us</span>
    <h1>ติดต่อเรา</h1>
    <p>พร้อมให้คำปรึกษาและตอบทุกคำถามของคุณ</p>
  </div>
</section>

<!-- Contact Section -->
<section class="contact-section">
  <div class="container">

    <?php if(isset($_GET['success'])): ?>
      <div class="contact-success">
        ✅ ส่งข้อความเรียบร้อยแล้ว เราจะติดต่อกลับเร็วที่สุด
      </div>
    <?php endif; ?>

    <div class="contact-grid">

      <!-- ══ Info Card ══ -->
      <div class="contact-info reveal">

        <h3>ข้อมูลติดต่อ</h3>

        <div class="info-item">
          <div class="info-icon">🏢</div>
          <div>
            <div class="info-label">ที่อยู่</div>
            <div class="info-text">
              128/90 หมู่ที่ 5 <br>
                ตำบลสันผีเสื้อ อำเภอเมืองเชียงใหม่
                จังหวัดเชียงใหม่ 50300
            </div>
          </div>
        </div>

        <div class="info-item">
          <div class="info-icon">📞</div>
          <div>
            <div class="info-label">โทรศัพท์</div>
            <div class="info-text">
              <a href="tel:021234567">093 - 614 - 9326</a>
            </div>
          </div>
        </div>

        <div class="info-item">
          <div class="info-icon">✉️</div>
          <div>
            <div class="info-label">อีเมล</div>
            <div class="info-text">
              <a href="mailto:info@pblr.co.th">pblthailand6@gmail.com</a>
            </div>
          </div>
        </div>

        <!-- <div class="info-item">
          <div class="info-icon">🕒</div>
          <div>
            <div class="info-label">เวลาทำการ</div>
            <div class="info-text">จันทร์ – ศุกร์</div>
            <div class="hours-badge">
              <span class="dot"></span>
              09:00 – 18:00 น.
            </div>
          </div>
        </div> -->

      </div>


      <!-- ══ Form Card ══ -->
      <div class="contact-form reveal reveal-delay-1">

        <h3>ส่งข้อความถึงเรา</h3>

        <form method="post" action="send-contact.php">

          <div class="form-row">
            <div class="form-field">
              <label>ชื่อของคุณ</label>
              <input type="text"
                     name="name"
                     class="form-control"
                     placeholder="ชื่อ – นามสกุล"
                     required>
            </div>

            <div class="form-field">
              <label>อีเมล</label>
              <input type="email"
                     name="email"
                     class="form-control"
                     placeholder="example@email.com"
                     required>
            </div>
          </div>

          <div class="form-field">
            <label>หัวข้อ</label>
            <input type="text"
                   name="subject"
                   class="form-control"
                   placeholder="หัวข้อที่ต้องการสอบถาม">
          </div>

          <div class="form-field">
            <label>ข้อความ</label>
            <textarea name="message"
                      class="form-control"
                      placeholder="รายละเอียดที่ต้องการติดต่อ..."
                      required></textarea>
          </div>

          <button type="submit" class="btn-send">
            ส่งข้อความ
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
          </button>

        </form>

      </div>

    </div>
  </div>
</section>


<!-- Map -->
<section class="map-section">
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d60424.68442448418!2d98.955773!3d18.818519000000002!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30da3bb35e3a8f89%3A0x1c0346c928fabe80!2z4LmA4LiK4Li14Lii4LiH4LmD4Lir4Lih4LmIIDUwMzAwIOC4m-C4o-C4sOC5gOC4l-C4qOC5hOC4l-C4og!5e0!3m2!1sth!2s!4v1773641013653!5m2!1sth!2s" 
    width="400" 
    height="300" 
    style="border:0;" 
    allowfullscreen="" 
    loading="lazy" 
    referrerpolicy="no-referrer-when-downgrade">
</iframe>
</section>


<!-- Scroll reveal -->
<script>
const io = new IntersectionObserver(entries => {
  entries.forEach((e, i) => {
    if (e.isIntersecting) {
      setTimeout(() => e.target.classList.add('on'), i * 100);
      io.unobserve(e.target);
    }
  });
}, { threshold: 0.12 });

document.querySelectorAll('.reveal').forEach(el => io.observe(el));
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>