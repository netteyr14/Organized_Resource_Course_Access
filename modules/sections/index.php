<?php
// ============================================================
//  modules/sections/index.php  —  Sections list
// ============================================================
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit; }

require_once '../../config/db.php';

// Fetch all sections joined with their year level, ordered naturally
$sections = $pdo->query(
    "SELECT s.*, y.year_level
     FROM sections s
     JOIN year_levels y ON s.year_id = y.year_id
     ORDER BY y.year_id ASC, s.section_name ASC"
)->fetchAll();

// Fetch year levels for the sidebar panel
$year_levels = $pdo->query("SELECT * FROM year_levels ORDER BY year_id ASC")->fetchAll();

// Group sections by year level for display
$grouped = [];
foreach ($sections as $s) {
    $grouped[$s['year_level']][] = $s;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sections — ORCA</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
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
    body { background: var(--bg); font-family: 'DM Sans', sans-serif; color: var(--text); min-height: 100vh; }

    /* ── Navbar ── */
    .top-nav {
      background: var(--brand); padding: 0 2rem;
      display: flex; align-items: center; justify-content: space-between;
      height: 56px; position: sticky; top: 0; z-index: 100;
      box-shadow: 0 1px 4px rgba(0,0,0,.18);
    }
    .nav-brand { font-family: 'DM Serif Display', serif; font-size: 1.15rem; color: #fff; text-decoration: none; }
    .nav-links { display: flex; align-items: center; gap: .25rem; list-style: none; }
    .nav-links a {
      color: rgba(255,255,255,.75); text-decoration: none; font-size: .875rem;
      font-weight: 500; padding: .4rem .75rem; border-radius: 6px;
      transition: background .15s, color .15s;
    }
    .nav-links a:hover, .nav-links a.active { background: rgba(255,255,255,.12); color: #fff; }
    .nav-user { display: flex; align-items: center; gap: .75rem; font-size: .82rem; color: rgba(255,255,255,.65); }
    .nav-user .badge-role {
      background: rgba(200,169,110,.25); color: var(--accent);
      border: 1px solid rgba(200,169,110,.35); border-radius: 20px;
      padding: .15rem .6rem; font-size: .72rem; font-weight: 500;
      letter-spacing: .05em; text-transform: uppercase;
    }
    .nav-user a {
      color: rgba(255,255,255,.55); text-decoration: none; font-size: .82rem;
      padding: .3rem .6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,.18);
      transition: background .15s, color .15s;
    }
    .nav-user a:hover { background: rgba(255,255,255,.1); color: #fff; }

    /* ── Layout ── */
    .page-wrap { max-width: 1000px; margin: 0 auto; padding: 2.5rem 1.5rem; }

    .page-header {
      display: flex; align-items: flex-end;
      justify-content: space-between;
      margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;
    }
    .page-header h2 { font-family: 'DM Serif Display', serif; font-size: 1.65rem; color: var(--brand); }
    .page-header p  { font-size: .85rem; color: var(--muted); margin-top: .2rem; }
    .accent-line { width: 40px; height: 3px; background: linear-gradient(90deg, var(--brand), var(--accent)); border-radius: 2px; margin-top: .6rem; }

    .btn-add {
      display: inline-flex; align-items: center; gap: .45rem;
      background: var(--brand); color: #fff; text-decoration: none;
      font-size: .875rem; font-weight: 500;
      padding: .55rem 1.1rem; border-radius: 8px;
      transition: background .15s; white-space: nowrap;
    }
    .btn-add:hover { background: var(--brand-mid); color: #fff; }
    .btn-add svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2.2; stroke-linecap: round; }

    /* ── Two-column layout ── */
    .content-grid {
      display: grid;
      grid-template-columns: 1fr 260px;
      gap: 1.25rem;
      align-items: start;
    }

    /* ── Flash ── */
    .flash {
      padding: .7rem 1rem; border-radius: 8px; font-size: .875rem;
      margin-bottom: 1.25rem; display: flex; align-items: center; gap: .5rem;
    }
    .flash-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
    .flash-error   { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

    /* ── Table card ── */
    .table-card {
      background: var(--card-bg); border: 1px solid var(--border);
      border-radius: var(--radius); overflow: hidden;
    }

    .year-group-header {
      background: #f9f8f5;
      padding: .6rem 1.1rem;
      font-size: .72rem;
      font-weight: 500;
      letter-spacing: .09em;
      text-transform: uppercase;
      color: var(--muted);
      border-bottom: 1px solid var(--border);
    }

    table { width: 100%; border-collapse: collapse; }
    tbody tr { border-bottom: 1px solid var(--border); transition: background .1s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #faf9f6; }
    tbody td { padding: .8rem 1.1rem; font-size: .9rem; vertical-align: middle; }

    .section-badge {
      display: inline-block;
      background: #fdf3e3; color: #9a6e2a;
      font-size: .8rem; font-weight: 500;
      padding: .2rem .6rem; border-radius: 6px;
    }

    .actions { display: flex; align-items: center; gap: .5rem; }
    .btn-edit, .btn-delete {
      display: inline-flex; align-items: center; gap: .3rem;
      font-size: .8rem; font-weight: 500;
      padding: .3rem .65rem; border-radius: 6px;
      text-decoration: none; transition: background .15s;
      border: 1px solid transparent;
    }
    .btn-edit  { background: #eef4fb; color: var(--brand-mid); border-color: #ccdff0; }
    .btn-edit:hover  { background: #ddeaf8; color: var(--brand); }
    .btn-delete { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
    .btn-delete:hover { background: #fee2e2; color: #991b1b; }
    .btn-edit svg, .btn-delete svg {
      width: 13px; height: 13px; stroke: currentColor; fill: none;
      stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
    }

    .empty-state { text-align: center; padding: 2.5rem 1.5rem; }
    .empty-state p { font-size: .9rem; color: var(--muted); margin-top: .4rem; }

    /* ── Year levels sidebar card ── */
    .side-card {
      background: var(--card-bg); border: 1px solid var(--border);
      border-radius: var(--radius); overflow: hidden;
    }
    .side-card-header {
      padding: .8rem 1.1rem;
      background: #f9f8f5;
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
    }
    .side-card-header span {
      font-size: .72rem; font-weight: 500;
      letter-spacing: .09em; text-transform: uppercase; color: var(--muted);
    }
    .side-card ul { list-style: none; padding: .5rem 0; }
    .side-card ul li {
      padding: .55rem 1.1rem;
      font-size: .875rem;
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
    }
    .side-card ul li:last-child { border-bottom: none; }
    .year-count {
      font-size: .75rem; color: var(--muted);
      background: #f5f3ef; border: 1px solid var(--border);
      padding: .1rem .45rem; border-radius: 20px;
    }

    .page-footer { text-align: center; margin-top: 3rem; font-size: .78rem; color: var(--muted); }
  </style>
</head>
<body>

  <nav class="top-nav">
    <a class="nav-brand" href="../../index.php">Organized Resource Course Access</a>
    <ul class="nav-links">
      <li><a href="../../index.php">Dashboard</a></li>
      <li><a href="../courses/index.php">Courses</a></li>
      <li><a href="index.php" class="active">Sections</a></li>
      <li><a href="../schedules/index.php">Schedules</a></li>
      <?php if ($_SESSION['role'] === 'admin'): ?>
      <li><a href="../users/index.php">Users</a></li>
      <?php endif; ?>
    </ul>
    <div class="nav-user">
      <span><?= htmlspecialchars($_SESSION['username']) ?></span>
      <span class="badge-role"><?= htmlspecialchars($_SESSION['role']) ?></span>
      <a href="../../logout.php">Sign out</a>
    </div>
  </nav>

  <div class="page-wrap">

    <div class="page-header">
      <div>
        <h2>Sections</h2>
        <p>Class sections organized by year level.</p>
        <div class="accent-line"></div>
      </div>
      <a href="add.php" class="btn-add">
        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add section
      </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
      <div class="flash flash-success">
        <?php
          echo htmlspecialchars(match($_GET['success']) {
            'added'   => 'Section added successfully.',
            'updated' => 'Section updated successfully.',
            'deleted' => 'Section deleted.',
            default   => 'Action completed.'
          });
        ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
      <div class="flash flash-error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="content-grid">

      <!-- Sections table grouped by year level -->
      <div>
        <?php if (empty($sections)): ?>
          <div class="table-card">
            <div class="empty-state">
              <p>No sections found. <a href="add.php">Add the first one.</a></p>
            </div>
          </div>
        <?php else: ?>
          <?php foreach ($grouped as $year_label => $rows): ?>
            <div class="table-card" style="margin-bottom: 1rem;">
              <div class="year-group-header"><?= htmlspecialchars($year_label) ?></div>
              <table>
                <tbody>
                  <?php foreach ($rows as $s): ?>
                  <tr>
                    <td><span class="section-badge"><?= htmlspecialchars($s['section_name']) ?></span></td>
                    <td style="color:var(--muted); font-size:.82rem;">
                      Added <?= date('M j, Y', strtotime($s['created_at'])) ?>
                    </td>
                    <td>
                      <div class="actions">
                        <a href="edit.php?id=<?= $s['section_id'] ?>" class="btn-edit">
                          <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                          Edit
                        </a>
                        <a href="delete.php?id=<?= $s['section_id'] ?>" class="btn-delete"
                           onclick="return confirm('Delete section <?= htmlspecialchars(addslashes($s['section_name'])) ?>? This cannot be undone.')">
                          <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                          Delete
                        </a>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Year levels sidebar -->
      <div class="side-card">
        <div class="side-card-header">
          <span>Year levels</span>
        </div>
        <ul>
          <?php foreach ($year_levels as $y):
            $count = isset($grouped[$y['year_level']]) ? count($grouped[$y['year_level']]) : 0;
          ?>
          <li>
            <?= htmlspecialchars($y['year_level']) ?>
            <span class="year-count"><?= $count ?> section<?= $count !== 1 ? 's' : '' ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

    </div>
  </div>

  <p class="page-footer">
    Colegio De Sta. Teresa De Avila &mdash; School of Information Technology &copy; <?= date('Y') ?>
  </p>

</body>
</html>