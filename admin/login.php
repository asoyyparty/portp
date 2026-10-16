<?php
require_once __DIR__ . '/auth.php';

// Redirect if already logged in
if (is_authenticated()) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = "Sesi tidak valid (CSRF token mismatch). Silakan refresh halaman.";
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = "Harap masukkan username dan password.";
        } else {
            if (attempt_login($username, $password, $error)) {
                header('Location: /admin/dashboard.php');
                exit;
            }
        }
    }
}

$csrf_token = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login — Qois Abdul Qudus Portfolio</title>
  <link rel="icon" type="image/svg+xml" href="/assets/icons/icon.svg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&family=Geist:wght@350;400;500;600&family=Geist+Mono:wght@400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/tokens.css" />

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #08090C;
      color: #E2E8F0;
      font-family: var(--font-body, 'Geist', sans-serif);
      padding: 20px;
      position: relative;
      overflow-x: hidden;
    }

    /* Ambient Background Glow */
    .ambient-glow {
      position: fixed;
      width: 500px;
      height: 500px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(235, 77, 109, 0.12) 0%, rgba(14, 165, 233, 0.05) 50%, transparent 70%);
      pointer-events: none;
      z-index: 0;
      filter: blur(40px);
    }
    .glow-1 { top: -100px; right: -100px; }
    .glow-2 { bottom: -100px; left: -100px; background: radial-gradient(circle, rgba(14, 165, 233, 0.1) 0%, transparent 70%); }

    /* Login Container Card */
    .login-card {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 420px;
      background: rgba(18, 20, 29, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 20px;
      padding: 40px 32px;
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7),
                  0 0 1px 1px rgba(255, 255, 255, 0.05) inset;
      animation: cardAppear 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    @keyframes cardAppear {
      from { opacity: 0; transform: translateY(16px) scale(0.98); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Brand Header */
    .brand-box {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      margin-bottom: 30px;
    }
    .brand-logo {
      width: 56px;
      height: 56px;
      border-radius: 14px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
      margin-bottom: 16px;
      transition: transform 0.3s ease;
    }
    .brand-logo:hover {
      transform: scale(1.05) rotate(-3deg);
    }
    .brand-title {
      font-family: var(--font-display, 'Archivo', sans-serif);
      font-size: 20px;
      font-weight: 700;
      color: #FFFFFF;
      letter-spacing: -0.02em;
    }
    .brand-sub {
      font-family: var(--font-mono, 'Geist Mono', monospace);
      font-size: 11px;
      color: #94A3B8;
      margin-top: 4px;
      letter-spacing: 0.05em;
      text-transform: uppercase;
    }

    /* Alert Banner */
    .alert-box {
      background: rgba(239, 68, 68, 0.12);
      border: 1px solid rgba(239, 68, 68, 0.3);
      color: #FCA5A5;
      font-size: 13px;
      padding: 12px 14px;
      border-radius: 10px;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      gap: 10px;
      animation: shake 0.4s ease both;
    }
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%, 60% { transform: translateX(-5px); }
      40%, 80% { transform: translateX(5px); }
    }

    /* Form Styles */
    .form-group {
      margin-bottom: 20px;
      position: relative;
    }
    .form-label {
      display: block;
      font-family: var(--font-mono, 'Geist Mono', monospace);
      font-size: 11px;
      font-weight: 500;
      color: #94A3B8;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }
    .form-input {
      width: 100%;
      background: rgba(10, 12, 18, 0.8);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      padding: 13px 16px;
      color: #F8FAFC;
      font-family: var(--font-body, 'Geist', sans-serif);
      font-size: 14px;
      transition: all 0.2s ease;
      outline: none;
    }
    .form-input:focus {
      border-color: #EB4D6D;
      background: rgba(15, 17, 26, 0.95);
      box-shadow: 0 0 0 3px rgba(235, 77, 109, 0.15);
    }
    .toggle-pw-btn {
      position: absolute;
      right: 14px;
      background: none;
      border: none;
      color: #64748B;
      cursor: pointer;
      font-size: 13px;
      padding: 4px;
      transition: color 0.2s;
    }
    .toggle-pw-btn:hover {
      color: #CBD5E1;
    }

    /* Submit Button */
    .btn-submit {
      width: 100%;
      background: linear-gradient(135deg, #EB4D6D 0%, #D82D50 100%);
      color: #FFFFFF;
      border: none;
      border-radius: 12px;
      padding: 14px 20px;
      font-family: var(--font-display, 'Archivo', sans-serif);
      font-size: 14px;
      font-weight: 700;
      letter-spacing: 0.02em;
      cursor: pointer;
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
      box-shadow: 0 4px 16px rgba(235, 77, 109, 0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin-top: 10px;
    }
    .btn-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(235, 77, 109, 0.45);
      background: linear-gradient(135deg, #F0617E 0%, #EB4D6D 100%);
    }
    .btn-submit:active {
      transform: translateY(0);
    }

    /* Footer Back Link */
    .back-box {
      text-align: center;
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.06);
    }
    .back-link {
      color: #64748B;
      font-size: 13px;
      text-decoration: none;
      transition: color 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .back-link:hover {
      color: #CBD5E1;
    }
  </style>
</head>
<body>

  <div class="ambient-glow glow-1"></div>
  <div class="ambient-glow glow-2"></div>

  <div class="login-card">
    <div class="brand-box">
      <img src="/assets/icons/icon-192.png" alt="Q Logo" class="brand-logo" />
      <h1 class="brand-title">Control Center</h1>
      <p class="brand-sub">Portfolio Management System</p>
    </div>

    <?php if ($error): ?>
      <div class="alert-box" role="alert">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <span><?= htmlspecialchars($error) ?></span>
      </div>
    <?php endif; ?>

    <form method="POST" action="/admin/login.php" autocomplete="off">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>" />

      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <div class="input-wrapper">
          <input 
            type="text" 
            id="username" 
            name="username" 
            class="form-input" 
            placeholder="Masukkan username" 
            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
            required 
            autofocus 
          />
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="input-wrapper">
          <input 
            type="password" 
            id="password" 
            name="password" 
            class="form-input" 
            placeholder="Masukkan password" 
            required 
          />
          <button type="button" class="toggle-pw-btn" id="togglePwBtn" aria-label="Lihat password">
            <svg id="eyeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-submit">
        <span>Masuk ke Dashboard</span>
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
      </button>
    </form>

    <div class="back-box">
      <a href="/" class="back-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        <span>Kembali ke Halaman Portfolio</span>
      </a>
    </div>
  </div>

  <script>
    const toggleBtn = document.getElementById('togglePwBtn');
    const pwInput = document.getElementById('password');
    let show = false;

    toggleBtn.addEventListener('click', () => {
      show = !show;
      pwInput.type = show ? 'text' : 'password';
      toggleBtn.style.color = show ? '#EB4D6D' : '#64748B';
    });
  </script>

</body>
</html>
