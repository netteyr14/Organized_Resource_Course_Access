<?php
// ============================================================
//  modules/users/edit.php  —  Edit an existing user
// ============================================================
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit; }
if ($_SESSION['role'] !== 'admin') { header('Location: ../../index.php'); exit; }

require_once '../../config/db.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id === 0) { header('Location: index.php'); exit; }

// Fetch existing record
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) { header('Location: index.php?error=User+not+found.'); exit; }

$errors = [];
$input  = [
    'username' => $user['username'],
    'email'    => $user['email'],
    'role'     => $user['role'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input['username'] = trim($_POST['username'] ?? '');
    $input['email']    = trim($_POST['email']    ?? '');
    $input['role']     = trim($_POST['role']     ?? 'professor');
    $password         = $_POST['password']         ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validate
    if ($input['username'] === '')                           $errors[] = 'Username is required.';
    if (strlen($input['username']) < 3)                      $errors[] = 'Username must be at least 3 characters.';
    if ($input['email'] === '')                              $errors[] = 'Email is required.';
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (!in_array($input['role'], ['admin','professor']))    $errors[] = 'Invalid role selected.';

    // Password is optional on edit — only validate if filled in
    if ($password !== '') {
        if (strlen($password) < 8)       $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $password_confirm) $errors[] = 'Passwords do not match.';
    }

    // Duplicate checks — exclude current record
    if (empty($errors)) {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND user_id != ?");
        $chk->execute([$input['username'], $id]);
        if ($chk->fetchColumn() > 0) $errors[] = 'Username is already taken.';
    }
    if (empty($errors)) {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND user_id != ?");
        $chk->execute([$input['email'], $id]);
        if ($chk->fetchColumn() > 0) $errors[] = 'Email is already registered.';
    }

    if (empty($errors)) {
        if ($password !== '') {
            // Update with new password
            $stmt = $pdo->prepare(
                "UPDATE users SET username = ?, email = ?, role = ?, password = ? WHERE user_id = ?"
            );
            $stmt->execute([
                $input['username'],
                $input['email'],
                $input['role'],
                password_hash($password, PASSWORD_BCRYPT),
                $id,
            ]);
        } else {
            // Update without touching password
            $stmt = $pdo->prepare(
                "UPDATE users SET username = ?, email = ?, role = ? WHERE user_id = ?"
            );
            $stmt->execute([$input['username'], $input['email'], $input['role'], $id]);
        }
        header('Location: index.php?success=updated');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit User — ORCA</title>
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

    .page-wrap { max-width: 620px; margin: 0 auto; padding: 2.5rem 1.5rem; }

    .page-header { margin-bottom: 1.75rem; }
    .breadcrumb-nav {
      display: flex; align-items: center; gap: .4rem;
      font-size: .8rem; color: var(--muted); margin-bottom: .75rem;
    }
    .breadcrumb-nav a { color: var(--muted); text-decoration: none; }
    .breadcrumb-nav a:hover { color: var(--brand); }
    .breadcrumb-nav span { color: var(--border); }
    .page-header h2 { font-family: 'DM Serif Display', serif; font-size: 1.65rem; color: var(--brand); }
    .page-header .user-hint { font-size: .82rem; color: var(--muted); margin-top: .25rem; }
    .accent-line { width: 40px; height: 3px; background: linear-gradient(90deg, var(--brand), var(--accent)); border-radius: 2px; margin-top: .6rem; }

    .form-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 2rem; }

    .error-list {
      background: #fef2f2; border: 1px solid #fecaca;
      border-radius: 8px; padding: .75rem 1rem;
      margin-bottom: 1.5rem; font-size: .875rem; color: #991b1b;
    }
    .error-list ul { margin: .35rem 0 0 1rem; }
    .error-list li { margin-bottom: .15rem; }

    .field { margin-bottom: 1.25rem; }
    .field label {
      display: block; font-size: .78rem; font-weight: 500;
      letter-spacing: .05em; text-transform: uppercase;
      color: var(--muted); margin-bottom: .4rem;
    }
    .field input, .field select {
      width: 100%; padding: .65rem .85rem;
      border: 1px solid var(--border); border-radius: 8px;
      font-size: .95rem; font-family: 'DM Sans', sans-serif;
      color: var(--text); background: #fafaf8;
      transition: border-color .15s, box-shadow .15s;
    }
    .field input:focus, .field select:focus {
      outline: none; border-color: var(--brand-mid);
      box-shadow: 0 0 0 3px rgba(43,95,142,.12); background: #fff;
    }
    .field .hint { font-size: .77rem; color: var(--muted); margin-top: .35rem; }
    .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

    .divider { border: none; border-top: 1px solid var(--border); margin: 1.5rem 0 1.25rem; }
    .section-label {
      font-size: .72rem; font-weight: 500; letter-spacing: .09em;
      text-transform: uppercase; color: var(--muted); margin-bottom: 1rem;
    }

    .form-actions {
      display: flex; align-items: center; gap: .75rem;
      margin-top: 1.75rem; padding-top: 1.5rem; border-top: 1px solid var(--border);
    }
    .btn-save {
      padding: .65rem 1.4rem; background: var(--brand); color: #fff;
      border: none; border-radius: 8px; font-family: 'DM Sans', sans-serif;
      font-size: .925rem; font-weight: 500; cursor: pointer; transition: background .15s;
    }
    .btn-save:hover { background: var(--brand-mid); }
    .btn-cancel {
      padding: .65rem 1.1rem; background: transparent; color: var(--muted);
      border: 1px solid var(--border); border-radius: 8px;
      font-family: 'DM Sans', sans-serif; font-size: .925rem;
      text-decoration: none; transition: border-color .15s, color .15s;
    }
    .btn-cancel:hover { border-color: #b5b0a8; color: var(--text); }

    .page-footer { text-align: center; margin-top: 3rem; font-size: .78rem; color: var(--muted); }
  </style>
</head>
<body>

  <nav class="top-nav">
    <a class="nav-brand" href="../../index.php">Organized Resource Course Access</a>
    <ul class="nav-links">
      <li><a href="../../index.php">Dashboard</a></li>
      <li><a href="../courses/index.php">Courses</a></li>
      <li><a href="../sections/index.php">Sections</a></li>
      <li><a href="../schedules/index.php">Schedules</a></li>
      <li><a href="index.php" class="active">Users</a></li>
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
        <a href="index.php">Users</a>
        <span>/</span>
        <span>Edit user</span>
      </nav>
      <h2>Edit user</h2>
      <p class="user-hint">Editing: <strong><?= htmlspecialchars($user['username']) ?></strong></p>
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

      <form method="POST" action="edit.php?id=<?= $id ?>" novalidate>

        <p class="section-label">Account info</p>

        <div class="field-row">
          <div class="field">
            <label for="username">Username</label>
            <input
              type="text"
              id="username"
              name="username"
              value="<?= htmlspecialchars($input['username']) ?>"
              placeholder="e.g. jdelacruz"
              maxlength="50"
              required
              autofocus
            >
          </div>
          <div class="field">
            <label for="role">Role</label>
            <select id="role" name="role" required>
              <option value="professor" <?= $input['role'] === 'professor' ? 'selected' : '' ?>>Professor</option>
              <option value="admin"     <?= $input['role'] === 'admin'     ? 'selected' : '' ?>>Admin</option>
            </select>
          </div>
        </div>

        <div class="field">
          <label for="email">Email</label>
          <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars($input['email']) ?>"
            placeholder="e.g. jdelacruz@cstea.edu.ph"
            maxlength="100"
            required
          >
        </div>

        <hr class="divider">
        <p class="section-label">Change password <span style="font-weight:400;text-transform:none;letter-spacing:0;font-size:.8rem;">(leave blank to keep current)</span></p>

        <div class="field-row">
          <div class="field">
            <label for="password">New password</label>
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Min. 8 characters"
              autocomplete="new-password"
            >
          </div>
          <div class="field">
            <label for="password_confirm">Confirm password</label>
            <input
              type="password"
              id="password_confirm"
              name="password_confirm"
              placeholder="Repeat new password"
              autocomplete="new-password"
            >
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-save">Save changes</button>
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