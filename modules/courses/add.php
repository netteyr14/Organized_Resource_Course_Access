<?php
// ============================================================
//  modules/courses/add.php  —  Add a new course
// ============================================================
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit; }

require_once '../../config/db.php';

$errors = [];
$input  = ['course_code' => '', 'course_name' => '', 'units' => 3, 'description' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input['course_code']  = trim($_POST['course_code']  ?? '');
    $input['course_name']  = trim($_POST['course_name']  ?? '');
    $input['units']        = (int) ($_POST['units']      ?? 3);
    $input['description']  = trim($_POST['description']  ?? '');

    // Validate
    if ($input['course_code'] === '')  $errors[] = 'Course code is required.';
    if ($input['course_name'] === '')  $errors[] = 'Course name is required.';
    if ($input['units'] < 1 || $input['units'] > 9) $errors[] = 'Units must be between 1 and 9.';

    // Check duplicate code
    if (empty($errors)) {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE course_code = ?");
        $chk->execute([$input['course_code']]);
        if ($chk->fetchColumn() > 0) $errors[] = 'Course code already exists.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO courses (course_code, course_name, units, description)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $input['course_code'],
            $input['course_name'],
            $input['units'],
            $input['description'] ?: null,
        ]);
        header('Location: index.php?success=added');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Course — Courseware</title>
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

    body {
      background: var(--bg);
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

    /* ── Page ── */
    .page-wrap { max-width: 620px; margin: 0 auto; padding: 2.5rem 1.5rem; }

    .page-header { margin-bottom: 1.75rem; }
    .breadcrumb-nav {
      display: flex;
      align-items: center;
      gap: .4rem;
      font-size: .8rem;
      color: var(--muted);
      margin-bottom: .75rem;
    }
    .breadcrumb-nav a { color: var(--muted); text-decoration: none; }
    .breadcrumb-nav a:hover { color: var(--brand); }
    .breadcrumb-nav span { color: var(--border); }

    .page-header h2 {
      font-family: 'DM Serif Display', serif;
      font-size: 1.65rem;
      color: var(--brand);
    }
    .accent-line {
      width: 40px; height: 3px;
      background: linear-gradient(90deg, var(--brand), var(--accent));
      border-radius: 2px; margin-top: .6rem;
    }

    /* ── Card ── */
    .form-card {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 2rem;
    }

    /* ── Error list ── */
    .error-list {
      background: #fef2f2;
      border: 1px solid #fecaca;
      border-radius: 8px;
      padding: .75rem 1rem;
      margin-bottom: 1.5rem;
      font-size: .875rem;
      color: #991b1b;
    }
    .error-list ul { margin: .35rem 0 0 1rem; }
    .error-list li { margin-bottom: .15rem; }

    /* ── Form fields ── */
    .field { margin-bottom: 1.25rem; }

    .field label {
      display: block;
      font-size: .78rem;
      font-weight: 500;
      letter-spacing: .05em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: .4rem;
    }

    .field input,
    .field select,
    .field textarea {
      width: 100%;
      padding: .65rem .85rem;
      border: 1px solid var(--border);
      border-radius: 8px;
      font-size: .95rem;
      font-family: 'DM Sans', sans-serif;
      color: var(--text);
      background: #fafaf8;
      transition: border-color .15s, box-shadow .15s;
    }

    .field input:focus,
    .field select:focus,
    .field textarea:focus {
      outline: none;
      border-color: var(--brand-mid);
      box-shadow: 0 0 0 3px rgba(43,95,142,.12);
      background: #fff;
    }

    .field textarea { resize: vertical; min-height: 90px; }

    .field .hint {
      font-size: .77rem;
      color: var(--muted);
      margin-top: .35rem;
    }

    .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

    /* ── Buttons ── */
    .form-actions {
      display: flex;
      align-items: center;
      gap: .75rem;
      margin-top: 1.75rem;
      padding-top: 1.5rem;
      border-top: 1px solid var(--border);
    }

    .btn-save {
      padding: .65rem 1.4rem;
      background: var(--brand);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-family: 'DM Sans', sans-serif;
      font-size: .925rem;
      font-weight: 500;
      cursor: pointer;
      transition: background .15s;
    }
    .btn-save:hover { background: var(--brand-mid); }

    .btn-cancel {
      padding: .65rem 1.1rem;
      background: transparent;
      color: var(--muted);
      border: 1px solid var(--border);
      border-radius: 8px;
      font-family: 'DM Sans', sans-serif;
      font-size: .925rem;
      text-decoration: none;
      transition: border-color .15s, color .15s;
    }
    .btn-cancel:hover { border-color: #b5b0a8; color: var(--text); }

    .page-footer { text-align: center; margin-top: 3rem; font-size: .78rem; color: var(--muted); }
  </style>
</head>
<body>

  <nav class="top-nav">
    <a class="nav-brand" href="../../index.php">Courseware</a>
    <ul class="nav-links">
      <li><a href="../../index.php">Dashboard</a></li>
      <li><a href="index.php" class="active">Courses</a></li>
      <li><a href="../sections/index.php">Sections</a></li>
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
      <nav class="breadcrumb-nav">
        <a href="../../index.php">Dashboard</a>
        <span>/</span>
        <a href="index.php">Courses</a>
        <span>/</span>
        <span>Add course</span>
      </nav>
      <h2>Add course</h2>
      <div class="accent-line"></div>
    </div>

    <div class="form-card">

      <?php if (!empty($errors)): ?>
        <div class="error-list">
          <strong>Please fix the following:</strong>
          <ul>
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="POST" action="add.php" novalidate>

        <div class="field-row">
          <div class="field">
            <label for="course_code">Course code</label>
            <input
              type="text"
              id="course_code"
              name="course_code"
              value="<?= htmlspecialchars($input['course_code']) ?>"
              placeholder="e.g. IT201"
              maxlength="20"
              required
              autofocus
            >
          </div>
          <div class="field">
            <label for="units">Units</label>
            <select id="units" name="units">
              <?php for ($u = 1; $u <= 9; $u++): ?>
                <option value="<?= $u ?>" <?= $input['units'] == $u ? 'selected' : '' ?>><?= $u ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>

        <div class="field">
          <label for="course_name">Course name</label>
          <input
            type="text"
            id="course_name"
            name="course_name"
            value="<?= htmlspecialchars($input['course_name']) ?>"
            placeholder="e.g. Information Management"
            maxlength="150"
            required
          >
        </div>

        <div class="field">
          <label for="description">Description <span style="font-weight:400;text-transform:none;letter-spacing:0">(optional)</span></label>
          <textarea
            id="description"
            name="description"
            placeholder="Brief description of the course…"
          ><?= htmlspecialchars($input['description']) ?></textarea>
          <p class="hint">Shown on the course list as a short summary.</p>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-save">Save course</button>
          <a href="index.php" class="btn-cancel">Cancel</a>
        </div>

      </form>
    </div>

  </div>

  <p class="page-footer">
    Colegio De Sta. Teresa De Avila &mdash; School of Information Technology &copy; <?= date('Y') ?>
  </p>

</body>
</html>