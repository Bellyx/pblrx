<?php
session_start();

// ตัวอย่าง error (เอาไปผูกจริงทีหลัง)
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

  <style>
    * {
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }

    body {
      margin: 0;
      min-height: 100vh;
      background: linear-gradient(135deg, #0f172a, #1e293b);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-card {
      background: #ffffff;
      width: 100%;
      max-width: 420px;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 20px 50px rgba(0,0,0,.25);
    }

    .login-card h2 {
      text-align: center;
      margin-bottom: 8px;
      font-weight: 700;
    }

    .login-card p {
      text-align: center;
      color: #64748b;
      margin-bottom: 32px;
      font-size: 14px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 6px;
      font-size: 14px;
      font-weight: 600;
    }

    .form-group input {
      width: 100%;
      padding: 12px 14px;
      border-radius: 10px;
      border: 1px solid #cbd5f5;
      font-size: 15px;
      outline: none;
      transition: .2s;
    }

    .form-group input:focus {
      border-color: #6366f1;
      box-shadow: 0 0 0 3px rgba(99,102,241,.15);
    }

    .btn-login {
      width: 100%;
      padding: 14px;
      border-radius: 12px;
      border: none;
      background: linear-gradient(135deg, #6366f1, #4f46e5);
      color: #fff;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: .2s;
    }

    .btn-login:hover {
      opacity: .9;
      transform: translateY(-1px);
    }

    .error {
      background: #fee2e2;
      color: #b91c1c;
      padding: 10px 14px;
      border-radius: 10px;
      margin-bottom: 20px;
      font-size: 14px;
      text-align: center;
    }

    .footer-text {
      margin-top: 24px;
      text-align: center;
      font-size: 12px;
      color: #94a3b8;
    }
  </style>
</head>
<body>

  <div class="login-card">
    <h2>Admin Login</h2>
    <p>เข้าสู่ระบบสำหรับผู้ดูแล</p>

    <?php if($error): ?>
      <div class="error">
        ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง
      </div>
    <?php endif; ?>

    <form method="post" action="login_process.php">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" required>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password">
      </div>

      <button type="submit" class="btn-login">
        เข้าสู่ระบบ
      </button>
    </form>

    <div class="footer-text">
      © <?= date('Y') ?> Admin Panel
    </div>
  </div>

</body>
</html>
