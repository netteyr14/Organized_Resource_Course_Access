<?php
// ============================================================
//  index.php  —  Dashboard
// ============================================================
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/db.php';

// Summary counts
$counts = [];
foreach (['courses' => 'courses', 'sections' => 'sections', 'schedules' => 'schedules', 'users' => 'users'] as $key => $table) {
    $counts[$key] = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Courseware</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
  <style>
    :root {
      --brand:     #1a3a5c;
      --brand-mid: #2b5f8e;
      --accent:    #c8a96e;
      --bg:        #f5f3ef;
      --card-bg:   #ffffff;
      --text:      #1c1c1e;
      --muted:     #6b7280;
      --border:    #dcd8d0;
      --radius:    12px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background-color: var(--bg);
      font-family: 'DM Sans', sans-serif;
      color: var(--text);
      min-height: 100vh;
    }

    /* ── Navbar ── */
    .top-nav {
      background: var(--brand);
      padding: 0 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 56px;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 1px 4px rgba(0,0,0,.18);
    }

    .nav-brand {
      font-family: 'DM Serif Display', serif;
      font-size: 1.15rem;
      color: #fff;
      text-decoration: none;
      letter-spacing: .01em;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: .25rem;
      list-style: none;
    }

    .nav-links a {
      color: rgba(255,255,255,.75);
      text-decoration: none;
      font-size: .875rem;
      font-weight: 500;
      padding: .4rem .75rem;
      border-radius: 6px;
      transition: background .15s, color .15s;
    }

    .nav-links a:hover,
    .nav-links a.active {
      background: rgba(255,255,255,.12);
      color: #fff;
    }

    .nav-user {
      display: flex;
      align-items: center;
      gap: .75rem;
      font-size: .82rem;
      color: rgba(255,255,255,.65);
    }

    .nav-user .badge-role {
      background: rgba(200,169,110,.25);
      color: var(--accent);
      border: 1px solid rgba(200,169,110,.35);
      border-radius: 20px;
      padding: .15rem .6rem;
      font-size: .72rem;
      font-weight: 500;
      letter-spacing: .05em;
      text-transform: uppercase;
    }

    .nav-user a {
      color: rgba(255,255,255,.55);
      text-decoration: none;
      font-size: .82rem;
      padding: .3rem .6rem;
      border-radius: 6px;
      border: 1px solid rgba(255,255,255,.18);
      transition: background .15s, color .15s;
    }

    .nav-user a:hover {
      background: rgba(255,255,255,.1);
      color: #fff;
    }

    /* ── Page body ── */
    .page-wrap {
      max-width: 1000px;
      margin: 0 auto;
      padding: 2.5rem 1.5rem;
    }

    /* ── Welcome ── */
    .welcome-bar {
      margin-bottom: 2.25rem;
    }

    .welcome-bar h2 {
      font-family: 'DM Serif Display', serif;
      font-size: 1.65rem;
      color: var(--brand);
      line-height: 1.2;
    }

    .welcome-bar p {
      font-size: .9rem;
      color: var(--muted);
      margin-top: .3rem;
    }

    .accent-line {
      width: 48px;
      height: 3px;
      background: linear-gradient(90deg, var(--brand), var(--accent));
      border-radius: 2px;
      margin-top: .75rem;
    }

    /* ── Stat cards ── */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 2.5rem;
    }

    .stat-card {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 1.5rem 1.25rem;
      display: flex;
      flex-direction: column;
      gap: .4rem;
      text-decoration: none;
      color: inherit;
      transition: box-shadow .15s, transform .12s, border-color .15s;
      position: relative;
      overflow: hidden;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      background: var(--card-accent, var(--brand));
      border-radius: 12px 12px 0 0;
    }

    .stat-card:hover {
      box-shadow: 0 4px 16px rgba(0,0,0,.08);
      transform: translateY(-2px);
      border-color: #c8c3bb;
    }

    .stat-label {
      font-size: .75rem;
      font-weight: 500;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: var(--muted);
    }

    .stat-number {
      font-family: 'DM Serif Display', serif;
      font-size: 2.4rem;
      color: var(--brand);
      line-height: 1;
    }

    .stat-sub {
      font-size: .8rem;
      color: var(--muted);
    }

    /* card accent colors */
    .card-courses   { --card-accent: #2b5f8e; }
    .card-sections  { --card-accent: #c8a96e; }
    .card-schedules { --card-accent: #4a7c59; }
    .card-users     { --card-accent: #8b5e83; }

    /* ── Quick nav ── */
    .section-title {
      font-size: .75rem;
      font-weight: 500;
      letter-spacing: .1em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 1rem;
    }

    .modules-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 1rem;
    }

    .module-card {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 1.25rem;
      text-decoration: none;
      color: inherit;
      display: flex;
      align-items: center;
      gap: 1rem;
      transition: box-shadow .15s, transform .12s, border-color .15s;
    }

    .module-card:hover {
      box-shadow: 0 4px 16px rgba(0,0,0,.08);
      transform: translateY(-2px);
      border-color: #c8c3bb;
    }

    .module-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      background: var(--icon-bg, #e8f0f8);
    }

    .module-icon svg {
      width: 20px;
      height: 20px;
      stroke: var(--icon-color, #2b5f8e);
      fill: none;
      stroke-width: 1.8;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .module-info strong {
      font-size: .95rem;
      font-weight: 500;
      color: var(--text);
      display: block;
    }

    .module-info span {
      font-size: .8rem;
      color: var(--muted);
    }

    /* icon bg variants */
    .icon-courses   { --icon-bg: #e8f0f8; --icon-color: #2b5f8e; }
    .icon-sections  { --icon-bg: #fdf3e3; --icon-color: #9a6e2a; }
    .icon-schedules { --icon-bg: #e8f4ec; --icon-color: #4a7c59; }
    .icon-users     { --icon-bg: #f3eaf5; --icon-color: #8b5e83; }

    /* ── Footer ── */
    .page-footer {
      text-align: center;
      margin-top: 3rem;
      font-size: .78rem;
      color: var(--muted);
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="top-nav">
    <a class="nav-brand" href="index.php">Courseware</a>
    <ul class="nav-links">
      <li><a href="index.php" class="active">Dashboard</a></li>
      <li><a href="modules/courses/index.php">Courses</a></li>
      <li><a href="modules/sections/index.php">Sections</a></li>
      <li><a href="modules/schedules/index.php">Schedules</a></li>
      <?php if ($_SESSION['role'] === 'admin'): ?>
      <li><a href="modules/users/index.php">Users</a></li>
      <?php endif; ?>
    </ul>
    <div class="nav-user">
      <span><?= htmlspecialchars($_SESSION['username']) ?></span>
      <span class="badge-role"><?= htmlspecialchars($_SESSION['role']) ?></span>
      <a href="logout.php">Sign out</a>
    </div>
  </nav>

  <div class="page-wrap">

    <!-- Welcome -->
    <div class="welcome-bar">
      <h2>Good <?= (date('H') < 12) ? 'morning' : ((date('H') < 18) ? 'afternoon' : 'evening') ?>,
          <?= htmlspecialchars($_SESSION['username']) ?>.</h2>
      <p>Here's a quick overview of the system.</p>
      <div class="accent-line"></div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <a href="modules/courses/index.php" class="stat-card card-courses">
        <span class="stat-label">Courses</span>
        <span class="stat-number"><?= $counts['courses'] ?></span>
        <span class="stat-sub">Total course records</span>
      </a>
      <a href="modules/sections/index.php" class="stat-card card-sections">
        <span class="stat-label">Sections</span>
        <span class="stat-number"><?= $counts['sections'] ?></span>
        <span class="stat-sub">Class sections</span>
      </a>
      <a href="modules/schedules/index.php" class="stat-card card-schedules">
        <span class="stat-label">Schedules</span>
        <span class="stat-number"><?= $counts['schedules'] ?></span>
        <span class="stat-sub">Active class slots</span>
      </a>
      <?php if ($_SESSION['role'] === 'admin'): ?>
      <a href="modules/users/index.php" class="stat-card card-users">
        <span class="stat-label">Users</span>
        <span class="stat-number"><?= $counts['users'] ?></span>
        <span class="stat-sub">System accounts</span>
      </a>
      <?php endif; ?>
    </div>

    <!-- Module quick-nav -->
    <p class="section-title">Manage</p>
    <div class="modules-grid">

      <a href="modules/courses/index.php" class="module-card">
        <div class="module-icon icon-courses">
          <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        </div>
        <div class="module-info">
          <strong>Courses</strong>
          <span>Add, edit, and remove course records</span>
        </div>
      </a>

      <a href="modules/sections/index.php" class="module-card">
        <div class="module-icon icon-sections">
          <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        </div>
        <div class="module-info">
          <strong>Sections</strong>
          <span>Manage year levels and class sections</span>
        </div>
      </a>

      <a href="modules/schedules/index.php" class="module-card">
        <div class="module-icon icon-schedules">
          <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div class="module-info">
          <strong>Schedules</strong>
          <span>Assign courses to sections and rooms</span>
        </div>
      </a>

      <?php if ($_SESSION['role'] === 'admin'): ?>
      <a href="modules/users/index.php" class="module-card">
        <div class="module-icon icon-users">
          <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="module-info">
          <strong>Users</strong>
          <span>Manage admin and professor accounts</span>
        </div>
      </a>
      <?php endif; ?>

    </div>

  </div>

  <p class="page-footer">
    Colegio De Sta. Teresa De Avila &mdash; School of Information Technology &copy; <?= date('Y') ?>
  </p>

</body>
</html>