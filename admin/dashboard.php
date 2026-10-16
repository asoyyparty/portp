<?php
require_once __DIR__ . '/auth.php';
require_auth();

// Load initial data
$contentFile = DATA_DIR . '/content.json';
$content = file_exists($contentFile) ? json_decode(file_get_contents($contentFile), true) : [];

$csrfToken = get_csrf_token();
$username = $_SESSION['username'] ?? 'doelkussoy';
$userName = $_SESSION['user_name'] ?? 'Qois Abdul Qudus';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Control Center — Qois Abdul Qudus</title>
  <link rel="icon" type="image/svg+xml" href="/assets/icons/icon.svg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&family=Geist:wght@350;400;500;600;700&family=Geist+Mono:wght@400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/tokens.css" />

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg-dark: #0A0C10;
      --bg-surface: #12151D;
      --bg-card: #181B26;
      --bg-card-hover: #1E2333;
      --border-color: rgba(255, 255, 255, 0.08);
      --border-accent: rgba(235, 77, 109, 0.3);
      --text-main: #F8FAFC;
      --text-muted: #94A3B8;
      --color-primary: #EB4D6D;
      --color-primary-hover: #F0617E;
      --color-cyan: #38BDF8;
      --color-green: #10B981;
    }

    body {
      background-color: var(--bg-dark);
      color: var(--text-main);
      font-family: var(--font-body, 'Geist', sans-serif);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      overflow-x: hidden;
    }

    /* ── Top Bar ── */
    .topbar {
      position: sticky;
      top: 0;
      z-index: 100;
      height: 64px;
      background: rgba(18, 21, 29, 0.85);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--border-color);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 28px;
    }
    .topbar-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      color: var(--text-main);
    }
    .topbar-logo {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
    }
    .topbar-title {
      font-family: var(--font-display, 'Archivo', sans-serif);
      font-size: 15px;
      font-weight: 700;
      letter-spacing: -0.01em;
    }
    .topbar-badge {
      font-family: var(--font-mono, 'Geist Mono', monospace);
      font-size: 10px;
      background: rgba(235, 77, 109, 0.15);
      color: var(--color-primary);
      border: 1px solid rgba(235, 77, 109, 0.3);
      padding: 2px 8px;
      border-radius: 999px;
      font-weight: 600;
      text-transform: uppercase;
    }
    .topbar-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .btn-secondary-sm {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--border-color);
      color: var(--text-muted);
      padding: 6px 14px;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 500;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-secondary-sm:hover {
      background: rgba(255, 255, 255, 0.1);
      color: var(--text-main);
      border-color: rgba(255, 255, 255, 0.2);
    }
    .btn-primary-sm {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: var(--color-primary);
      border: none;
      color: #FFF;
      padding: 6px 14px;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-primary-sm:hover {
      background: var(--color-primary-hover);
      transform: translateY(-1px);
    }
    .user-pill {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 4px 12px;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid var(--border-color);
      font-size: 12px;
      color: var(--text-muted);
    }
    .user-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--color-green);
      box-shadow: 0 0 8px var(--color-green);
    }

    /* ── Main Layout with Sidebar Tabs ── */
    .dashboard-container {
      display: flex;
      flex: 1;
      max-width: 1440px;
      width: 100%;
      margin: 0 auto;
      padding: 24px 28px;
      gap: 28px;
    }
    @media (max-width: 900px) {
      .dashboard-container {
        flex-direction: column;
        padding: 16px;
      }
    }

    /* ── Sidebar Navigation ── */
    .sidebar-nav {
      width: 240px;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    @media (max-width: 900px) {
      .sidebar-nav {
        width: 100%;
        flex-direction: row;
        overflow-x: auto;
        padding-bottom: 8px;
      }
    }
    .nav-tab-btn {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      background: transparent;
      border: 1px solid transparent;
      border-radius: 12px;
      color: var(--text-muted);
      font-family: var(--font-body, 'Geist', sans-serif);
      font-size: 13px;
      font-weight: 500;
      text-align: left;
      cursor: pointer;
      transition: all 0.2s;
      white-space: nowrap;
    }
    .nav-tab-btn:hover {
      background: rgba(255, 255, 255, 0.04);
      color: var(--text-main);
    }
    .nav-tab-btn.is-active {
      background: rgba(235, 77, 109, 0.12);
      border-color: rgba(235, 77, 109, 0.25);
      color: #FFF;
      font-weight: 600;
    }
    .nav-tab-btn svg {
      width: 18px;
      height: 18px;
      flex-shrink: 0;
    }
    .nav-tab-badge {
      margin-left: auto;
      font-family: var(--font-mono, 'Geist Mono', monospace);
      font-size: 10px;
      padding: 2px 7px;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.08);
      color: var(--text-muted);
    }
    .nav-tab-btn.is-active .nav-tab-badge {
      background: rgba(235, 77, 109, 0.25);
      color: var(--color-primary);
    }

    /* ── Content Panes ── */
    .content-area {
      flex: 1;
      min-width: 0;
    }
    .tab-pane {
      display: none;
      animation: fadeIn 0.3s ease both;
    }
    .tab-pane.is-active {
      display: block;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(6px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* ── Cards & Sections ── */
    .admin-card {
      background: var(--bg-surface);
      border: 1px solid var(--border-color);
      border-radius: 16px;
      padding: 28px;
      margin-bottom: 24px;
      position: relative;
    }
    .card-header-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 22px;
      padding-bottom: 16px;
      border-bottom: 1px solid var(--border-color);
      flex-wrap: wrap;
      gap: 12px;
    }
    .card-header-title {
      font-family: var(--font-display, 'Archivo', sans-serif);
      font-size: 17px;
      font-weight: 700;
      color: #FFF;
    }
    .card-header-desc {
      font-size: 12px;
      color: var(--text-muted);
      margin-top: 2px;
    }

    /* ── Form Controls ── */
    .form-grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 18px;
    }
    @media (max-width: 768px) {
      .form-grid-2 { grid-template-columns: 1fr; }
    }
    .form-group {
      margin-bottom: 18px;
    }
    .form-label {
      display: block;
      font-family: var(--font-mono, 'Geist Mono', monospace);
      font-size: 11px;
      font-weight: 600;
      color: #94A3B8;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 7px;
    }
    .form-control {
      width: 100%;
      background: var(--bg-dark);
      border: 1px solid var(--border-color);
      border-radius: 10px;
      padding: 11px 14px;
      color: #F8FAFC;
      font-family: var(--font-body, 'Geist', sans-serif);
      font-size: 13px;
      outline: none;
      transition: all 0.2s;
    }
    .form-control:focus {
      border-color: var(--color-primary);
      box-shadow: 0 0 0 3px rgba(235, 77, 109, 0.15);
      background: #0E1017;
    }
    textarea.form-control {
      resize: vertical;
      min-height: 85px;
      line-height: 1.5;
    }

    /* ── Project List Cards ── */
    .project-item {
      display: flex;
      align-items: center;
      gap: 16px;
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      padding: 16px;
      margin-bottom: 12px;
      transition: all 0.2s;
    }
    .project-item:hover {
      background: var(--bg-card-hover);
      border-color: rgba(255, 255, 255, 0.15);
      transform: translateY(-1px);
    }
    .project-item-thumb {
      width: 72px;
      height: 48px;
      border-radius: 6px;
      object-fit: cover;
      background: #000;
      flex-shrink: 0;
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .project-item-info {
      flex: 1;
      min-width: 0;
    }
    .project-item-title {
      font-size: 14px;
      font-weight: 600;
      color: #FFF;
      margin-bottom: 3px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .project-item-meta {
      font-size: 11px;
      color: var(--text-muted);
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    .category-chip {
      font-family: var(--font-mono, 'Geist Mono', monospace);
      font-size: 9px;
      padding: 2px 6px;
      border-radius: 4px;
      background: rgba(56, 189, 248, 0.12);
      color: var(--color-cyan);
      text-transform: uppercase;
      font-weight: 600;
    }
    .project-item-actions {
      display: flex;
      gap: 8px;
      flex-shrink: 0;
    }

    /* ── Modal ── */
    .modal-backdrop {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0, 0, 0, 0.75);
      backdrop-filter: blur(8px);
      z-index: 1000;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .modal-backdrop.is-open {
      display: flex;
    }
    .modal-box {
      width: 100%;
      max-width: 680px;
      max-height: 90vh;
      overflow-y: auto;
      background: var(--bg-surface);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 20px;
      padding: 28px;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8);
      animation: cardAppear 0.25s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
      padding-bottom: 14px;
      border-bottom: 1px solid var(--border-color);
    }
    .modal-title {
      font-family: var(--font-display, 'Archivo', sans-serif);
      font-size: 17px;
      font-weight: 700;
      color: #FFF;
    }
    .modal-close {
      background: none;
      border: none;
      color: var(--text-muted);
      cursor: pointer;
      font-size: 20px;
      padding: 4px;
    }
    .modal-close:hover { color: #FFF; }

    /* ── Floating Toast ── */
    .toast-container {
      position: fixed;
      top: 80px;
      right: 24px;
      z-index: 9999;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .toast-pill {
      background: rgba(24, 27, 38, 0.95);
      border: 1px solid var(--border-color);
      border-left: 4px solid var(--color-primary);
      border-radius: 10px;
      padding: 12px 20px;
      color: #FFF;
      font-size: 13px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
      display: flex;
      align-items: center;
      gap: 10px;
      animation: slideInRight 0.3s ease both;
    }
    .toast-pill.is-success { border-left-color: var(--color-green); }
    .toast-pill.is-error { border-left-color: #EF4444; }
    @keyframes slideInRight {
      from { opacity: 0; transform: translateX(30px); }
      to { opacity: 1; transform: translateX(0); }
    }

    /* ── Mobile Layout & Touch Utilities ── */
    html, body {
      max-width: 100vw;
      overflow-x: hidden;
    }

    .card-actions-group {
      display: flex;
      gap: 10px;
      align-items: center;
    }

    .upload-row {
      display: flex;
      gap: 10px;
      align-items: center;
    }

    .modal-footer {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 24px;
    }

    @media (max-width: 900px) {
      .dashboard-container {
        flex-direction: column;
        padding: 12px 14px;
        gap: 14px;
      }
      .sidebar-nav {
        position: sticky;
        top: 56px;
        z-index: 95;
        width: calc(100% + 28px);
        margin: -12px -14px 14px -14px;
        padding: 10px 14px;
        background: rgba(10, 12, 16, 0.94);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-bottom: 1px solid var(--border-color);
        flex-direction: row;
        overflow-x: auto;
        overflow-y: hidden;
        gap: 8px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
      }
      .sidebar-nav::-webkit-scrollbar {
        display: none;
      }
      .nav-tab-btn {
        flex-shrink: 0;
        padding: 8px 14px;
        font-size: 13px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid var(--border-color);
      }
      .nav-tab-btn svg {
        width: 15px;
        height: 15px;
      }
      .nav-tab-btn.is-active {
        background: var(--color-primary);
        border-color: var(--color-primary);
        color: #FFF;
        box-shadow: 0 4px 14px rgba(235, 77, 109, 0.4);
      }
      .nav-tab-btn.is-active .nav-tab-badge {
        background: rgba(255, 255, 255, 0.25);
        color: #FFF;
      }
    }

    @media (max-width: 768px) {
      .topbar {
        height: 56px;
        padding: 0 12px;
      }
      .topbar-brand {
        gap: 8px;
      }
      .topbar-logo {
        width: 28px;
        height: 28px;
      }
      .topbar-title {
        font-size: 13.5px;
        max-width: 140px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
      .topbar-badge {
        display: none;
      }
      .topbar-actions {
        gap: 6px;
      }
      .topbar-actions .btn-text,
      .user-pill .user-name {
        display: none;
      }
      .user-pill {
        padding: 6px 8px;
      }
      .topbar-actions .btn-secondary-sm {
        padding: 7px 9px;
        min-height: 36px;
      }

      .admin-card {
        padding: 16px 14px;
        border-radius: 14px;
        margin-bottom: 14px;
      }
      .card-header-bar {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
        margin-bottom: 18px;
        padding-bottom: 12px;
      }
      .card-header-title {
        font-size: 16px;
      }
      .card-header-desc {
        font-size: 12px;
      }
      .card-header-bar > .btn-primary-sm,
      .card-header-bar > .btn-secondary-sm {
        width: 100%;
        justify-content: center;
        padding: 10px 14px;
        min-height: 42px;
      }
      .card-actions-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        width: 100%;
      }
      .card-actions-group .btn-primary-sm,
      .card-actions-group .btn-secondary-sm {
        width: 100%;
        justify-content: center;
        min-height: 40px;
        padding: 8px 10px;
        font-size: 12px;
      }
      .form-control {
        font-size: 14px;
        padding: 10px 12px;
        min-height: 42px;
      }
      textarea.form-control {
        min-height: 80px;
      }
      #formChangePw button,
      #tab-security a.btn-secondary-sm {
        width: 100%;
        justify-content: center;
        min-height: 42px;
      }
    }

    @media (max-width: 640px) {
      .project-item {
        display: grid;
        grid-template-columns: 56px 1fr;
        grid-template-areas:
          "thumb info"
          "actions actions";
        gap: 10px 12px;
        padding: 12px;
        border-radius: 12px;
      }
      .project-item-thumb {
        grid-area: thumb;
        width: 56px;
        height: 44px;
      }
      .project-item-info {
        grid-area: info;
        min-width: 0;
      }
      .project-item-title {
        font-size: 13.5px;
        white-space: normal;
        line-height: 1.3;
        margin-bottom: 4px;
      }
      .project-item-meta {
        font-size: 10.5px;
        gap: 6px;
      }
      .project-item-actions {
        grid-area: actions;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        width: 100%;
        padding-top: 10px;
        border-top: 1px solid rgba(255, 255, 255, 0.06);
      }
      .project-item-actions .btn-secondary-sm {
        width: 100%;
        justify-content: center;
        padding: 8px;
        min-height: 38px;
      }

      .modal-backdrop {
        padding: 0;
        align-items: flex-end;
      }
      .modal-box {
        max-height: 88vh;
        border-radius: 20px 20px 0 0;
        padding: 20px 16px 28px;
        width: 100%;
        animation: slideUpModal 0.28s cubic-bezier(0.16, 1, 0.3, 1) both;
      }
      @keyframes slideUpModal {
        from { opacity: 0; transform: translateY(100%); }
        to { opacity: 1; transform: translateY(0); }
      }
      .modal-header {
        margin-bottom: 16px;
        padding-bottom: 12px;
      }
      .upload-row {
        flex-direction: column;
        align-items: stretch;
      }
      .upload-row .btn-secondary-sm {
        width: 100%;
        justify-content: center;
        min-height: 42px;
      }
      .modal-footer {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 20px;
      }
      .modal-footer button {
        width: 100%;
        justify-content: center;
        min-height: 44px;
      }

      .toast-container {
        top: 66px;
        left: 14px;
        right: 14px;
        width: auto;
      }
      .toast-pill {
        padding: 10px 14px;
        font-size: 12.5px;
        width: 100%;
      }
    }

    @media (max-width: 480px) {
      .topbar-title {
        max-width: 110px;
      }
      .card-actions-group {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

  <!-- Top Bar -->
  <header class="topbar">
    <a href="/" class="topbar-brand" target="_blank" title="Buka website portfolio utama">
      <img src="/assets/icons/icon-192.png" alt="Q Logo" class="topbar-logo" />
      <span class="topbar-title">Qois Portfolio Manager</span>
      <span class="topbar-badge">Admin</span>
    </a>

    <div class="topbar-actions">
      <button class="btn-secondary-sm" id="btnSyncHtml" title="Re-generate index.html untuk sinkronisasi GitHub Pages">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
        <span class="btn-text">Sync index.html</span>
      </button>

      <a href="/" target="_blank" class="btn-secondary-sm" title="Lihat Live Portfolio">
        <span class="btn-text">Lihat Live</span>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
      </a>

      <div class="user-pill" title="Login sebagai <?= htmlspecialchars($username) ?>">
        <span class="user-dot"></span>
        <span class="user-name"><?= htmlspecialchars($username) ?></span>
      </div>

      <a href="/admin/logout.php" class="btn-secondary-sm" style="color:#F87171;" title="Logout">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
        <span class="btn-text">Logout</span>
      </a>
    </div>
  </header>

  <!-- Main Container -->
  <div class="dashboard-container">

    <!-- Sidebar Navigation Tabs -->
    <aside class="sidebar-nav">
      <button class="nav-tab-btn is-active" data-target="tab-projects">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
        <span>Proyek & Karya</span>
        <span class="nav-tab-badge" id="badgeProjectCount"><?= count($content['projects'] ?? []) ?></span>
      </button>

      <button class="nav-tab-btn" data-target="tab-profile">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        <span>Profil & Hero</span>
      </button>

      <button class="nav-tab-btn" data-target="tab-experience">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
        <span>Pengalaman & Edukasi</span>
      </button>

      <button class="nav-tab-btn" data-target="tab-certifications">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>
        <span>Sertifikasi & Lisensi</span>
        <span class="nav-tab-badge" id="badgeCertCount"><?= count($content['certifications'] ?? []) ?></span>
      </button>

      <button class="nav-tab-btn" data-target="tab-services">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
        <span>Layanan & Keahlian</span>
      </button>

      <button class="nav-tab-btn" data-target="tab-contact">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
        <span>Kontak & Sosial</span>
      </button>

      <button class="nav-tab-btn" data-target="tab-security">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        <span>Keamanan & Backup</span>
      </button>
    </aside>

    <!-- Content Area -->
    <main class="content-area">

      <!-- ══════════ TAB 1: PROJECTS MANAGER ══════════ -->
      <section id="tab-projects" class="tab-pane is-active">
        <div class="admin-card">
          <div class="card-header-bar">
            <div>
              <h2 class="card-header-title">Daftar Proyek Portofolio</h2>
              <p class="card-header-desc">Kelola aplikasi yang tampil di portfolio. Anda dapat menambah, mengedit teks, memperbarui screenshot, atau menghapus proyek.</p>
            </div>
            <button class="btn-primary-sm" id="btnOpenAddModal">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
              <span>Tambah Proyek Baru</span>
            </button>
          </div>

          <div id="projectsListContainer">
            <?php foreach (($content['projects'] ?? []) as $p): ?>
              <div class="project-item" data-id="<?= htmlspecialchars($p['id']) ?>">
                <img src="/<?= htmlspecialchars(!empty($p['image']) ? $p['image'] : 'assets/images/mapcba.png') ?>" alt="Cover" class="project-item-thumb" />
                <div class="project-item-info">
                  <div class="project-item-title"><?= htmlspecialchars($p['title']) ?></div>
                  <div class="project-item-meta">
                    <span class="category-chip"><?= htmlspecialchars($p['category'] ?? 'enterprise') ?></span>
                    <span><?= htmlspecialchars($p['org'] ?? '') ?></span>
                    <span>•</span>
                    <span><?= htmlspecialchars($p['period'] ?? '') ?></span>
                  </div>
                </div>
                <div class="project-item-actions">
                  <button class="btn-secondary-sm btn-edit-proj" data-json="<?= htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8') ?>">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    <span>Edit</span>
                  </button>
                  <button class="btn-secondary-sm btn-delete-proj" data-id="<?= htmlspecialchars($p['id']) ?>" data-title="<?= htmlspecialchars($p['title']) ?>" style="color:#F87171;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    <span>Hapus</span>
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <!-- ══════════ TAB 2: PROFILE & HERO ══════════ -->
      <section id="tab-profile" class="tab-pane">
        <div class="admin-card">
          <div class="card-header-bar">
            <div>
              <h2 class="card-header-title">Identitas Profil & Hero Section</h2>
              <p class="card-header-desc">Sesuaikan teks perkenalan, headline, dan data metrik di bagian paling atas portfolio.</p>
            </div>
            <button class="btn-primary-sm" id="btnSaveProfile">
              <span>Simpan Perubahan</span>
            </button>
          </div>

          <form id="formProfile">
            <div class="form-grid-2">
              <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($content['profile']['name'] ?? '') ?>" />
              </div>
              <div class="form-group">
                <label class="form-label">Role / Spesialisasi</label>
                <input type="text" class="form-control" name="role" value="<?= htmlspecialchars($content['profile']['role'] ?? '') ?>" />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Headline / Tagline Utama</label>
              <input type="text" class="form-control" name="tagline" value="<?= htmlspecialchars($content['profile']['tagline'] ?? '') ?>" />
            </div>

            <div class="form-group">
              <label class="form-label">Deskripsi Bio Singkat</label>
              <textarea class="form-control" name="bio" rows="3"><?= htmlspecialchars($content['profile']['bio'] ?? '') ?></textarea>
            </div>

            <div class="form-grid-2">
              <div class="form-group">
                <label class="form-label">Status Ketersediaan (Pill Banner)</label>
                <input type="text" class="form-control" name="status_text" value="<?= htmlspecialchars($content['profile']['status_text'] ?? '') ?>" />
              </div>
              <div class="form-group">
                <label class="form-label">Lokasi Domisili</label>
                <input type="text" class="form-control" name="location" value="<?= htmlspecialchars($content['profile']['location'] ?? '') ?>" />
              </div>
            </div>

            <h3 style="font-size:14px; margin:24px 0 12px; color:#FFF; font-weight:700;">Metrik Angka Pencapaian (Hero Counters)</h3>
            <div class="form-grid-2">
              <?php foreach (($content['metrics'] ?? []) as $i => $m): ?>
                <div style="background:var(--bg-card); padding:14px; border-radius:10px; border:1px solid var(--border-color);">
                  <div class="form-group" style="margin-bottom:8px;">
                    <label class="form-label">Nilai / Angka</label>
                    <input type="text" class="form-control metric-value" data-index="<?= $i ?>" value="<?= htmlspecialchars($m['value']) ?>" />
                  </div>
                  <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Label Deskripsi</label>
                    <input type="text" class="form-control metric-label" data-index="<?= $i ?>" value="<?= htmlspecialchars($m['label']) ?>" />
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </form>
        </div>
      </section>

      <!-- ══════════ TAB 3: EXPERIENCE & EDUCATION ══════════ -->
      <section id="tab-experience" class="tab-pane">
        <div class="admin-card">
          <div class="card-header-bar">
            <div>
              <h2 class="card-header-title">Pengalaman Kerja (Work Experience)</h2>
              <p class="card-header-desc">Perbarui linimasa karier profesional Anda di PT Centa Brasindo Abadi dan organisasi lainnya.</p>
            </div>
            <div class="card-actions-group">
              <button class="btn-secondary-sm" id="btnAddExperience" type="button">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Tambah Pengalaman</span>
              </button>
              <button class="btn-primary-sm" id="btnSaveExperience" type="button">
                <span>Simpan Pengalaman & Edukasi</span>
              </button>
            </div>
          </div>

          <div id="experienceList">
            <?php foreach (($content['experience'] ?? []) as $i => $exp): ?>
              <div class="admin-card exp-card-item" style="background:var(--bg-card); margin-bottom:16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:8px;">
                  <span style="font-size:13px; font-weight:600; color:var(--color-primary-light);">Pengalaman #<?= $i + 1 ?></span>
                  <button type="button" class="btn-secondary-sm btn-delete-exp" style="color:#F87171; padding:4px 10px; font-size:12px;">Hapus</button>
                </div>
                <div class="form-grid-2">
                  <div class="form-group">
                    <label class="form-label">Perusahaan / Organisasi</label>
                    <input type="text" class="form-control exp-company" value="<?= htmlspecialchars($exp['company'] ?? '') ?>" />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Jabatan / Posisi</label>
                    <input type="text" class="form-control exp-role" value="<?= htmlspecialchars($exp['role'] ?? '') ?>" />
                  </div>
                </div>
                <div class="form-grid-2">
                  <div class="form-group">
                    <label class="form-label">Periode Waktu</label>
                    <input type="text" class="form-control exp-period" value="<?= htmlspecialchars($exp['period'] ?? '') ?>" />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Lokasi</label>
                    <input type="text" class="form-control exp-location" value="<?= htmlspecialchars($exp['location'] ?? '') ?>" />
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Deskripsi Pekerjaan</label>
                  <textarea class="form-control exp-desc" rows="3"><?= htmlspecialchars($exp['desc'] ?? '') ?></textarea>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Section Education -->
          <div style="margin-top:40px; padding-top:24px; border-top:1px solid rgba(255,255,255,0.08);">
            <div class="card-header-bar" style="margin-bottom:16px;">
              <div>
                <h2 class="card-header-title">Riwayat Pendidikan (Education)</h2>
                <p class="card-header-desc">Kelola gelar sarjana, institusi perguruan tinggi, periode, dan keterangan riset.</p>
              </div>
              <button class="btn-secondary-sm" id="btnAddEducation" type="button">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Tambah Pendidikan</span>
              </button>
            </div>

            <div id="educationList">
              <?php foreach (($content['education'] ?? []) as $i => $edu): ?>
                <div class="admin-card edu-card-item" style="background:var(--bg-card); margin-bottom:16px;">
                  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:8px;">
                    <span style="font-size:13px; font-weight:600; color:var(--color-primary-light);">Pendidikan #<?= $i + 1 ?></span>
                    <button type="button" class="btn-secondary-sm btn-delete-edu" style="color:#F87171; padding:4px 10px; font-size:12px;">Hapus</button>
                  </div>
                  <div class="form-grid-2">
                    <div class="form-group">
                      <label class="form-label">Jenjang & Program Studi</label>
                      <input type="text" class="form-control edu-degree" value="<?= htmlspecialchars($edu['degree'] ?? '') ?>" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">Institusi / Universitas</label>
                      <input type="text" class="form-control edu-institution" value="<?= htmlspecialchars($edu['institution'] ?? '') ?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="form-label">Periode Waktu</label>
                    <input type="text" class="form-control edu-period" value="<?= htmlspecialchars($edu['period'] ?? '') ?>" />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Deskripsi / Detail Pendidikan</label>
                    <textarea class="form-control edu-desc" rows="3"><?= htmlspecialchars($edu['desc'] ?? '') ?></textarea>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

        </div>
      </section>

      <!-- ══════════ TAB: CERTIFICATIONS ══════════ -->
      <section id="tab-certifications" class="tab-pane">
        <div class="admin-card">
          <div class="card-header-bar">
            <div>
              <h2 class="card-header-title">Sertifikasi & Lisensi Profesional</h2>
              <p class="card-header-desc">Kelola 10+ sertifikasi resmi (AWS, Dicoding, Kemendikbudristek SIB, DBS, dll) beserta link kredensial.</p>
            </div>
            <div class="card-actions-group">
              <button class="btn-secondary-sm" id="btnAddCert" type="button">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Tambah Sertifikasi</span>
              </button>
              <button class="btn-primary-sm" id="btnSaveCertifications" type="button">
                <span>Simpan Sertifikasi</span>
              </button>
            </div>
          </div>

          <div id="certificationsList">
            <?php foreach (($content['certifications'] ?? []) as $i => $cert): ?>
              <div class="admin-card cert-card-item" style="background:var(--bg-card); margin-bottom:16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:8px;">
                  <span style="font-size:13px; font-weight:600; color:var(--color-primary-light);">Sertifikasi #<span class="cert-item-num"><?= $i + 1 ?></span></span>
                  <button type="button" class="btn-secondary-sm btn-delete-cert" style="color:#F87171; padding:4px 10px; font-size:12px;">Hapus</button>
                </div>
                <div class="form-grid-2">
                  <div class="form-group">
                    <label class="form-label">Nama Sertifikasi / Lisensi</label>
                    <input type="text" class="form-control cert-title" value="<?= htmlspecialchars($cert['title'] ?? '') ?>" />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Penerbit / Institusi / ID</label>
                    <input type="text" class="form-control cert-issuer" value="<?= htmlspecialchars($cert['issuer'] ?? '') ?>" />
                  </div>
                </div>
                <div class="form-grid-2">
                  <div class="form-group">
                    <label class="form-label">Link Kredensial (Verify URL / LinkedIn)</label>
                    <input type="text" class="form-control cert-url" value="<?= htmlspecialchars($cert['url'] ?? '') ?>" />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Teks Tombol Aksi</label>
                    <input type="text" class="form-control cert-action-text" value="<?= htmlspecialchars($cert['action_text'] ?? 'Show Credential ↗') ?>" />
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Ringkasan Materi / Cakupan Kompetensi</label>
                  <textarea class="form-control cert-summary" rows="2"><?= htmlspecialchars($cert['summary'] ?? '') ?></textarea>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <!-- ══════════ TAB 4: SERVICES & PILLARS ══════════ -->
      <section id="tab-services" class="tab-pane">
        <div class="admin-card">
          <div class="card-header-bar">
            <div>
              <h2 class="card-header-title">Pilar Layanan & Spesialisasi</h2>
              <p class="card-header-desc">Tiga pilar utama keahlian teknis Anda (IT Infrastructure, Software Engineering, EHS Automation).</p>
            </div>
            <button class="btn-primary-sm" id="btnSaveServices">
              <span>Simpan Layanan</span>
            </button>
          </div>

          <div id="servicesList">
            <?php foreach (($content['services'] ?? []) as $i => $srv): ?>
              <div class="admin-card" style="background:var(--bg-card); margin-bottom:16px;">
                <div class="form-group">
                  <label class="form-label">Judul Layanan #<?= htmlspecialchars($srv['number'] ?? ($i+1)) ?></label>
                  <input type="text" class="form-control srv-title" value="<?= htmlspecialchars($srv['title']) ?>" />
                </div>
                <div class="form-group">
                  <label class="form-label">Deskripsi Detail</label>
                  <textarea class="form-control srv-desc" rows="3"><?= htmlspecialchars($srv['desc']) ?></textarea>
                </div>
                <div class="form-group">
                  <label class="form-label">Skill Tags (Pisahkan dengan koma)</label>
                  <input type="text" class="form-control srv-tags" value="<?= htmlspecialchars(implode(', ', $srv['tags'] ?? [])) ?>" />
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <!-- ══════════ TAB 5: CONTACT & SOCIALS ══════════ -->
      <section id="tab-contact" class="tab-pane">
        <div class="admin-card">
          <div class="card-header-bar">
            <div>
              <h2 class="card-header-title">Kontak & Tautan Sosial Media</h2>
              <p class="card-header-desc">Kelola link WhatsApp, LinkedIn, GitHub, Instagram, dan email publik.</p>
            </div>
            <button class="btn-primary-sm" id="btnSaveContact">
              <span>Simpan Kontak</span>
            </button>
          </div>

          <form id="formContact">
            <div class="form-grid-2">
              <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($content['contact']['email'] ?? '') ?>" />
              </div>
              <div class="form-group">
                <label class="form-label">Nomor WhatsApp</label>
                <input type="text" class="form-control" name="whatsapp" value="<?= htmlspecialchars($content['contact']['whatsapp'] ?? '') ?>" />
              </div>
            </div>

            <div class="form-grid-2">
              <div class="form-group">
                <label class="form-label">Link WhatsApp (wa.me)</label>
                <input type="url" class="form-control" name="whatsapp_url" value="<?= htmlspecialchars($content['contact']['whatsapp_url'] ?? '') ?>" />
              </div>
              <div class="form-group">
                <label class="form-label">LinkedIn Profile URL</label>
                <input type="url" class="form-control" name="linkedin" value="<?= htmlspecialchars($content['contact']['linkedin'] ?? '') ?>" />
              </div>
            </div>

            <div class="form-grid-2">
              <div class="form-group">
                <label class="form-label">GitHub URL</label>
                <input type="url" class="form-control" name="github" value="<?= htmlspecialchars($content['contact']['github'] ?? '') ?>" />
              </div>
              <div class="form-group">
                <label class="form-label">Instagram URL</label>
                <input type="url" class="form-control" name="instagram" value="<?= htmlspecialchars($content['contact']['instagram'] ?? '') ?>" />
              </div>
            </div>
          </form>
        </div>
      </section>

      <!-- ══════════ TAB 6: SECURITY & BACKUP ══════════ -->
      <section id="tab-security" class="tab-pane">
        <div class="admin-card">
          <div class="card-header-bar">
            <div>
              <h2 class="card-header-title">Ubah Password Admin (doelkussoy)</h2>
              <p class="card-header-desc">Ganti password akun kontrol portofolio Anda secara berkala.</p>
            </div>
          </div>

          <form id="formChangePw" style="max-width:440px;">
            <div class="form-group">
              <label class="form-label">Password Lama</label>
              <input type="password" class="form-control" id="oldPassword" required />
            </div>
            <div class="form-group">
              <label class="form-label">Password Baru</label>
              <input type="password" class="form-control" id="newPassword" required minlength="6" />
            </div>
            <div class="form-group">
              <label class="form-label">Konfirmasi Password Baru</label>
              <input type="password" class="form-control" id="confirmPassword" required minlength="6" />
            </div>
            <button type="submit" class="btn-primary-sm" style="padding:10px 18px;">
              <span>Update Password</span>
            </button>
          </form>
        </div>

        <div class="admin-card">
          <div class="card-header-bar">
            <div>
              <h2 class="card-header-title">Cadangkan & Ekspor Data (Backup)</h2>
              <p class="card-header-desc">Unduh seluruh konten portofolio Anda dalam format file JSON untuk arsip cadangan.</p>
            </div>
          </div>
          <a href="/admin/api.php?action=export_backup" class="btn-secondary-sm" style="padding:10px 18px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            <span>Unduh File Backup JSON</span>
          </a>
        </div>
      </section>

    </main>
  </div>

  <!-- ══════════ PROJECT MODAL (ADD / EDIT) ══════════ -->
  <div class="modal-backdrop" id="projectModal">
    <div class="modal-box">
      <div class="modal-header">
        <h3 class="modal-title" id="modalTitle">Tambah Proyek Baru</h3>
        <button type="button" class="modal-close" id="btnCloseModal">&times;</button>
      </div>

      <form id="formProjectModal">
        <input type="hidden" id="projId" />

        <div class="form-group">
          <label class="form-label">Judul Proyek *</label>
          <input type="text" class="form-control" id="projTitle" placeholder="contoh: CBA Network Digital Twin 3D" required />
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Organisasi / Perusahaan</label>
            <input type="text" class="form-control" id="projOrg" placeholder="contoh: PT Centa Brasindo Abadi Chemical Industry" />
          </div>
          <div class="form-group">
            <label class="form-label">Periode Pengerjaan</label>
            <input type="text" class="form-control" id="projPeriod" placeholder="contoh: 2026 – Present" />
          </div>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Kategori Filter</label>
            <select class="form-control" id="projCategory">
              <option value="enterprise">Infra & Enterprise</option>
              <option value="web">Web Applications</option>
              <option value="mobile">Mobile & Research</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Badge Status</label>
            <input type="text" class="form-control" id="projBadge" placeholder="contoh: Active / Deployed / Published" />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Deskripsi Proyek</label>
          <textarea class="form-control" id="projDesc" rows="3" placeholder="Jelaskan sistem dan manfaatnya..."></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Tech Tags (Pisahkan dengan koma)</label>
          <input type="text" class="form-control" id="projTags" placeholder="contoh: React 18, Three.js, ReactFlow, Express, MySQL" />
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">URL Live Demo (Opsional)</label>
            <input type="url" class="form-control" id="projDemo" placeholder="https://..." />
          </div>
          <div class="form-group">
            <label class="form-label">URL GitHub / Repository</label>
            <input type="url" class="form-control" id="projGithub" placeholder="https://github.com/asoyyparty/..." />
          </div>
        </div>

        <!-- Image Upload Section -->
        <div class="form-group">
          <label class="form-label">Path Gambar Cover</label>
          <div class="upload-row">
            <input type="text" class="form-control" id="projImage" placeholder="assets/images/..." />
            <label class="btn-secondary-sm" style="cursor:pointer; flex-shrink:0; height:40px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
              <span>Upload Gambar</span>
              <input type="file" id="fileUploadInput" accept="image/png,image/jpeg,image/webp,image/svg+xml" style="display:none;" />
            </label>
          </div>
          <div id="imagePreviewBox" style="margin-top:10px; display:none;">
            <img id="imgPreview" src="" alt="Preview" style="max-width:200px; max-height:100px; border-radius:6px; border:1px solid var(--border-color);" />
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-secondary-sm" id="btnCancelModal">Batal</button>
          <button type="submit" class="btn-primary-sm" style="padding:10px 22px;">Simpan Proyek</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Toast Container -->
  <div class="toast-container" id="toastBox"></div>

  <!-- Client-side Logic Script -->
  <script>
    // ── Helper: Show Toast Notification ──
    function showToast(message, type = 'success') {
      const box = document.getElementById('toastBox');
      const pill = document.createElement('div');
      pill.className = `toast-pill is-${type}`;
      pill.innerHTML = `
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
        <span>${message}</span>
      `;
      box.appendChild(pill);
      setTimeout(() => {
        pill.style.opacity = '0';
        pill.style.transform = 'translateX(20px)';
        pill.style.transition = 'all 0.3s ease';
        setTimeout(() => pill.remove(), 300);
      }, 3500);
    }

    // ── Tab Navigation ──
    const tabBtns = document.querySelectorAll('.nav-tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        tabBtns.forEach(b => b.classList.remove('is-active'));
        tabPanes.forEach(p => p.classList.remove('is-active'));
        btn.classList.add('is-active');
        const targetId = btn.getAttribute('data-target');
        const targetPane = document.getElementById(targetId);
        if (targetPane) targetPane.classList.add('is-active');
        if (window.innerWidth <= 900) {
          btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
      });
    });

    // ── Sync HTML Button ──
    document.getElementById('btnSyncHtml').addEventListener('click', async () => {
      const btn = document.getElementById('btnSyncHtml');
      btn.disabled = true;
      btn.innerText = 'Menyinkronkan...';
      try {
        const res = await fetch('/admin/api.php?action=sync_html');
        const json = await res.json();
        if (json.success) {
          showToast(json.message, 'success');
        } else {
          showToast(json.message, 'error');
        }
      } catch (e) {
        showToast('Gagal menyinkronkan: ' + e.message, 'error');
      } finally {
        btn.disabled = false;
        btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg><span class="btn-text">Sync index.html</span>`;
      }
    });

    // ── Save Profile & Hero ──
    document.getElementById('btnSaveProfile').addEventListener('click', async () => {
      const form = document.getElementById('formProfile');
      const formData = new FormData(form);

      const profile = {
        name: formData.get('name'),
        role: formData.get('role'),
        tagline: formData.get('tagline'),
        bio: formData.get('bio'),
        status_text: formData.get('status_text'),
        location: formData.get('location')
      };

      const metrics = [];
      const valInputs = document.querySelectorAll('.metric-value');
      const lblInputs = document.querySelectorAll('.metric-label');
      valInputs.forEach((inp, idx) => {
        metrics.push({
          value: inp.value,
          label: lblInputs[idx] ? lblInputs[idx].value : ''
        });
      });

      try {
        const res = await fetch('/admin/api.php?action=save_profile', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ profile, metrics })
        });
        const json = await res.json();
        if (json.success) {
          showToast(json.message, 'success');
        } else {
          showToast(json.message, 'error');
        }
      } catch (err) {
        showToast(err.message, 'error');
      }
    });

    // ── Save Experience & Education ──
    document.getElementById('btnSaveExperience').addEventListener('click', async () => {
      const btn = document.getElementById('btnSaveExperience');
      btn.disabled = true;
      btn.innerText = 'Menyimpan...';

      const expCards = document.querySelectorAll('#experienceList .exp-card-item');
      const experience = [];
      expCards.forEach(card => {
        const company = card.querySelector('.exp-company')?.value.trim() || '';
        const role = card.querySelector('.exp-role')?.value.trim() || '';
        const period = card.querySelector('.exp-period')?.value.trim() || '';
        const location = card.querySelector('.exp-location')?.value.trim() || '';
        const desc = card.querySelector('.exp-desc')?.value.trim() || '';
        if (company || role) {
          experience.push({
            id: 'exp-' + Math.random().toString(36).substr(2, 6),
            company,
            role,
            period,
            location,
            desc
          });
        }
      });

      const eduCards = document.querySelectorAll('#educationList .edu-card-item');
      const education = [];
      eduCards.forEach(card => {
        const degree = card.querySelector('.edu-degree')?.value.trim() || '';
        const institution = card.querySelector('.edu-institution')?.value.trim() || '';
        const period = card.querySelector('.edu-period')?.value.trim() || '';
        const desc = card.querySelector('.edu-desc')?.value.trim() || '';
        if (degree || institution) {
          education.push({
            id: 'edu-' + Math.random().toString(36).substr(2, 6),
            degree,
            institution,
            period,
            desc
          });
        }
      });

      try {
        const res = await fetch('/admin/api.php?action=save_experience', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ experience, education })
        });
        const json = await res.json();
        showToast(json.message, json.success ? 'success' : 'error');
      } catch (err) {
        showToast('Gagal menyimpan: ' + err.message, 'error');
      } finally {
        btn.disabled = false;
        btn.innerHTML = '<span>Simpan Pengalaman & Edukasi</span>';
      }
    });

    // Add Experience Card
    document.getElementById('btnAddExperience').addEventListener('click', () => {
      const list = document.getElementById('experienceList');
      const count = list.querySelectorAll('.exp-card-item').length + 1;
      const card = document.createElement('div');
      card.className = 'admin-card exp-card-item';
      card.style.background = 'var(--bg-card)';
      card.style.marginBottom = '16px';
      card.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:8px;">
          <span style="font-size:13px; font-weight:600; color:var(--color-primary-light);">Pengalaman #${count}</span>
          <button type="button" class="btn-secondary-sm btn-delete-exp" style="color:#F87171; padding:4px 10px; font-size:12px;">Hapus</button>
        </div>
        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Perusahaan / Organisasi</label>
            <input type="text" class="form-control exp-company" placeholder="PT Contoh Industri" />
          </div>
          <div class="form-group">
            <label class="form-label">Jabatan / Posisi</label>
            <input type="text" class="form-control exp-role" placeholder="Software Engineer" />
          </div>
        </div>
        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Periode Waktu</label>
            <input type="text" class="form-control exp-period" placeholder="Nov 2024 – Present" />
          </div>
          <div class="form-group">
            <label class="form-label">Lokasi</label>
            <input type="text" class="form-control exp-location" placeholder="Kota Serang" />
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Deskripsi Pekerjaan</label>
          <textarea class="form-control exp-desc" rows="3" placeholder="Deskripsi tugas dan pencapaian..."></textarea>
        </div>
      `;
      list.appendChild(card);
    });

    // Delegated delete for experience
    document.getElementById('experienceList').addEventListener('click', (e) => {
      if (e.target.closest('.btn-delete-exp')) {
        if (confirm('Hapus item pengalaman ini?')) {
          e.target.closest('.exp-card-item').remove();
        }
      }
    });

    // Add Education Card
    document.getElementById('btnAddEducation').addEventListener('click', () => {
      const list = document.getElementById('educationList');
      const count = list.querySelectorAll('.edu-card-item').length + 1;
      const card = document.createElement('div');
      card.className = 'admin-card edu-card-item';
      card.style.background = 'var(--bg-card)';
      card.style.marginBottom = '16px';
      card.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:8px;">
          <span style="font-size:13px; font-weight:600; color:var(--color-primary-light);">Pendidikan #${count}</span>
          <button type="button" class="btn-secondary-sm btn-delete-edu" style="color:#F87171; padding:4px 10px; font-size:12px;">Hapus</button>
        </div>
        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Jenjang & Program Studi</label>
            <input type="text" class="form-control edu-degree" placeholder="Sarjana Komputer (S.Kom) — Teknik Informatika" />
          </div>
          <div class="form-group">
            <label class="form-label">Institusi / Universitas</label>
            <input type="text" class="form-control edu-institution" placeholder="Universitas Bina Bangsa, Serang" />
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Periode Waktu</label>
          <input type="text" class="form-control edu-period" placeholder="2020 – 2024" />
        </div>
        <div class="form-group">
          <label class="form-label">Deskripsi / Detail Pendidikan</label>
          <textarea class="form-control edu-desc" rows="3" placeholder="Fokus riset, skripsi, atau prestasi..."></textarea>
        </div>
      `;
      list.appendChild(card);
    });

    // Delegated delete for education
    document.getElementById('educationList').addEventListener('click', (e) => {
      if (e.target.closest('.btn-delete-edu')) {
        if (confirm('Hapus riwayat pendidikan ini?')) {
          e.target.closest('.edu-card-item').remove();
        }
      }
    });

    // ── Save Certifications ──
    document.getElementById('btnSaveCertifications').addEventListener('click', async () => {
      const btn = document.getElementById('btnSaveCertifications');
      btn.disabled = true;
      btn.innerText = 'Menyimpan...';

      const certCards = document.querySelectorAll('#certificationsList .cert-card-item');
      const certifications = [];
      certCards.forEach((card, idx) => {
        const title = card.querySelector('.cert-title')?.value.trim() || '';
        const issuer = card.querySelector('.cert-issuer')?.value.trim() || '';
        const url = card.querySelector('.cert-url')?.value.trim() || '';
        const action_text = card.querySelector('.cert-action-text')?.value.trim() || 'Show Credential ↗';
        const summary = card.querySelector('.cert-summary')?.value.trim() || '';
        if (title || issuer) {
          certifications.push({
            id: 'cert-' + (idx + 1),
            title,
            issuer,
            url,
            action_text,
            summary
          });
        }
      });

      try {
        const res = await fetch('/admin/api.php?action=save_certifications', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ certifications })
        });
        const json = await res.json();
        showToast(json.message, json.success ? 'success' : 'error');
        if (json.success) {
          const badge = document.getElementById('badgeCertCount');
          if (badge) badge.innerText = certifications.length;
        }
      } catch (err) {
        showToast('Gagal menyimpan: ' + err.message, 'error');
      } finally {
        btn.disabled = false;
        btn.innerHTML = '<span>Simpan Sertifikasi</span>';
      }
    });

    // Add Certification Card
    document.getElementById('btnAddCert').addEventListener('click', () => {
      const list = document.getElementById('certificationsList');
      const count = list.querySelectorAll('.cert-card-item').length + 1;
      const card = document.createElement('div');
      card.className = 'admin-card cert-card-item';
      card.style.background = 'var(--bg-card)';
      card.style.marginBottom = '16px';
      card.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:8px;">
          <span style="font-size:13px; font-weight:600; color:var(--color-primary-light);">Sertifikasi Baru #${count}</span>
          <button type="button" class="btn-secondary-sm btn-delete-cert" style="color:#F87171; padding:4px 10px; font-size:12px;">Hapus</button>
        </div>
        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Nama Sertifikasi / Lisensi</label>
            <input type="text" class="form-control cert-title" placeholder="Misal: AWS Certified Solutions Architect" />
          </div>
          <div class="form-group">
            <label class="form-label">Penerbit / Institusi / ID</label>
            <input type="text" class="form-control cert-issuer" placeholder="Misal: AWS • Amazon Web Services" />
          </div>
        </div>
        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Link Kredensial (Verify URL / LinkedIn)</label>
            <input type="text" class="form-control cert-url" placeholder="https://..." />
          </div>
          <div class="form-group">
            <label class="form-label">Teks Tombol Aksi</label>
            <input type="text" class="form-control cert-action-text" value="Show Credential ↗" placeholder="Show Credential ↗ / Verify Credential ↗" />
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Ringkasan Materi / Cakupan Kompetensi</label>
          <textarea class="form-control cert-summary" rows="2" placeholder="Ringkasan materi atau kompetensi yang dicakup..."></textarea>
        </div>
      `;
      list.appendChild(card);
    });

    // Delegated delete for certifications
    document.getElementById('certificationsList').addEventListener('click', (e) => {
      if (e.target.closest('.btn-delete-cert')) {
        if (confirm('Hapus sertifikasi ini?')) {
          e.target.closest('.cert-card-item').remove();
          document.querySelectorAll('#certificationsList .cert-item-num').forEach((el, i) => {
            el.innerText = i + 1;
          });
        }
      }
    });

    // ── Save Services ──
    document.getElementById('btnSaveServices').addEventListener('click', async () => {
      const titles = document.querySelectorAll('.srv-title');
      const descs = document.querySelectorAll('.srv-desc');
      const tags = document.querySelectorAll('.srv-tags');

      const services = [];
      titles.forEach((el, idx) => {
        services.push({
          number: '0' + (idx + 1),
          title: el.value,
          desc: descs[idx] ? descs[idx].value : '',
          tags: tags[idx] ? tags[idx].value.split(',').map(s => s.trim()).filter(Boolean) : []
        });
      });

      try {
        const res = await fetch('/admin/api.php?action=save_services', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ services })
        });
        const json = await res.json();
        showToast(json.message, json.success ? 'success' : 'error');
      } catch (e) {
        showToast(e.message, 'error');
      }
    });

    // ── Save Contact ──
    document.getElementById('btnSaveContact').addEventListener('click', async () => {
      const form = document.getElementById('formContact');
      const formData = new FormData(form);
      const contact = Object.fromEntries(formData.entries());

      try {
        const res = await fetch('/admin/api.php?action=save_contact', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ contact })
        });
        const json = await res.json();
        showToast(json.message, json.success ? 'success' : 'error');
      } catch (e) {
        showToast(e.message, 'error');
      }
    });

    // ── Change Password ──
    document.getElementById('formChangePw').addEventListener('submit', async (e) => {
      e.preventDefault();
      const oldPw = document.getElementById('oldPassword').value;
      const newPw = document.getElementById('newPassword').value;
      const confPw = document.getElementById('confirmPassword').value;

      if (newPw !== confPw) {
        showToast('Konfirmasi password tidak cocok!', 'error');
        return;
      }

      try {
        const res = await fetch('/admin/api.php?action=change_password', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ old_password: oldPw, new_password: newPw })
        });
        const json = await res.json();
        showToast(json.message, json.success ? 'success' : 'error');
        if (json.success) {
          document.getElementById('formChangePw').reset();
        }
      } catch (err) {
        showToast(err.message, 'error');
      }
    });

    // ── Project Modal Logic ──
    const modal = document.getElementById('projectModal');
    const modalTitle = document.getElementById('modalTitle');
    const btnCloseModal = document.getElementById('btnCloseModal');
    const btnCancelModal = document.getElementById('btnCancelModal');
    const btnOpenAdd = document.getElementById('btnOpenAddModal');

    function openModal(data = null) {
      if (data) {
        modalTitle.innerText = 'Edit Proyek: ' + data.title;
        document.getElementById('projId').value = data.id || '';
        document.getElementById('projTitle').value = data.title || '';
        document.getElementById('projOrg').value = data.org || '';
        document.getElementById('projPeriod').value = data.period || '';
        document.getElementById('projCategory').value = data.category || 'enterprise';
        document.getElementById('projBadge').value = data.badge || '';
        document.getElementById('projDesc').value = data.desc || '';
        document.getElementById('projTags').value = (data.tech_tags || []).join(', ');
        document.getElementById('projDemo').value = data.demo_url || '';
        document.getElementById('projGithub').value = data.github_url || '';
        document.getElementById('projImage').value = data.image || '';

        if (data.image) {
          document.getElementById('imgPreview').src = '/' + data.image;
          document.getElementById('imagePreviewBox').style.display = 'block';
        } else {
          document.getElementById('imagePreviewBox').style.display = 'none';
        }
      } else {
        modalTitle.innerText = 'Tambah Proyek Baru';
        document.getElementById('formProjectModal').reset();
        document.getElementById('projId').value = '';
        document.getElementById('imagePreviewBox').style.display = 'none';
      }
      modal.classList.add('is-open');
    }

    function closeModal() {
      modal.classList.remove('is-open');
    }

    btnOpenAdd.addEventListener('click', () => openModal(null));
    btnCloseModal.addEventListener('click', closeModal);
    btnCancelModal.addEventListener('click', closeModal);

    // Edit button click listeners
    document.querySelectorAll('.btn-edit-proj').forEach(btn => {
      btn.addEventListener('click', () => {
        const jsonStr = btn.getAttribute('data-json');
        try {
          const data = JSON.parse(jsonStr);
          openModal(data);
        } catch (e) {
          console.error(e);
        }
      });
    });

    // Delete button click listeners
    document.querySelectorAll('.btn-delete-proj').forEach(btn => {
      btn.addEventListener('click', async () => {
        const id = btn.getAttribute('data-id');
        const title = btn.getAttribute('data-title');
        if (confirm(`Yakin ingin menghapus proyek "${title}"?`)) {
          try {
            const res = await fetch('/admin/api.php?action=delete_project', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ id })
            });
            const json = await res.json();
            if (json.success) {
              showToast(json.message, 'success');
              btn.closest('.project-item').remove();
              const badge = document.getElementById('badgeProjectCount');
              badge.innerText = parseInt(badge.innerText) - 1;
            } else {
              showToast(json.message, 'error');
            }
          } catch (e) {
            showToast(e.message, 'error');
          }
        }
      });
    });

    // Image Upload Handler
    document.getElementById('fileUploadInput').addEventListener('change', async (e) => {
      const file = e.target.files[0];
      if (!file) return;

      const formData = new FormData();
      formData.append('image', file);

      showToast('Mengunggah gambar...', 'success');
      try {
        const res = await fetch('/admin/api.php?action=upload_image', {
          method: 'POST',
          body: formData
        });
        const json = await res.json();
        if (json.success) {
          document.getElementById('projImage').value = json.file_path;
          document.getElementById('imgPreview').src = '/' + json.file_path;
          document.getElementById('imagePreviewBox').style.display = 'block';
          showToast(json.message, 'success');
        } else {
          showToast(json.message, 'error');
        }
      } catch (err) {
        showToast('Gagal upload: ' + err.message, 'error');
      }
    });

    // Save Project Form
    document.getElementById('formProjectModal').addEventListener('submit', async (e) => {
      e.preventDefault();

      const project = {
        id: document.getElementById('projId').value,
        title: document.getElementById('projTitle').value,
        org: document.getElementById('projOrg').value,
        period: document.getElementById('projPeriod').value,
        category: document.getElementById('projCategory').value,
        badge: document.getElementById('projBadge').value,
        desc: document.getElementById('projDesc').value,
        tech_tags: document.getElementById('projTags').value.split(',').map(s => s.trim()).filter(Boolean),
        demo_url: document.getElementById('projDemo').value,
        github_url: document.getElementById('projGithub').value,
        image: document.getElementById('projImage').value
      };

      try {
        const res = await fetch('/admin/api.php?action=save_project', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ project })
        });
        const json = await res.json();
        if (json.success) {
          showToast(json.message, 'success');
          closeModal();
          setTimeout(() => location.reload(), 800);
        } else {
          showToast(json.message, 'error');
        }
      } catch (err) {
        showToast(err.message, 'error');
      }
    });
  </script>

</body>
</html>
