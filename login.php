<?php
// ============================================================
//  login.php
// ============================================================
session_start();

// Already logged in → go to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please fill in both fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Courseware</title>
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
      min-height: 100vh;
      background-color: var(--bg);
      background-image:
        radial-gradient(ellipse 80% 60% at 10% 90%, rgba(26,58,92,.07) 0%, transparent 70%),
        radial-gradient(ellipse 60% 50% at 90% 10%, rgba(200,169,110,.10) 0%, transparent 60%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'DM Sans', sans-serif;
      color: var(--text);
      padding: 1.5rem;
    }

    .login-wrap {
      width: 100%;
      max-width: 420px;
    }

    /* ── Header ── */
    .login-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .login-header .school-name {
      font-size: .75rem;
      font-weight: 500;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: .5rem;
    }

    .login-header h1 {
      font-family: 'DM Serif Display', serif;
      font-size: 2rem;
      color: var(--brand);
      line-height: 1.15;
    }

    .login-header .subtitle {
      font-size: .85rem;
      color: var(--muted);
      margin-top: .4rem;
    }

    /* ── Card ── */
    .login-card {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 2.25rem 2rem;
      box-shadow: 0 2px 8px rgba(0,0,0,.06), 0 0 0 1px rgba(0,0,0,.03);
    }

    /* ── Form ── */
    .form-label {
      font-size: .8rem;
      font-weight: 500;
      letter-spacing: .04em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: .4rem;
    }

    .form-control {
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: .65rem .85rem;
      font-size: .95rem;
      color: var(--text);
      background: #fafaf8;
      transition: border-color .15s, box-shadow .15s;
    }

    .form-control:focus {
      border-color: var(--brand-mid);
      box-shadow: 0 0 0 3px rgba(43,95,142,.12);
      background: #fff;
      outline: none;
    }

    .mb-field { margin-bottom: 1.25rem; }

    /* ── Error alert ── */
    .alert-login {
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #991b1b;
      border-radius: 8px;
      padding: .65rem .85rem;
      font-size: .88rem;
      margin-bottom: 1.25rem;
      display: flex;
      align-items: center;
      gap: .5rem;
    }

    .alert-login::before {
      content: '';
      display: inline-block;
      width: 14px;
      height: 14px;
      flex-shrink: 0;
      background: currentColor;
      mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='currentColor'%3E%3Cpath fill-rule='evenodd' d='M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z' clip-rule='evenodd'/%3E%3C/svg%3E") center/contain no-repeat;
    }

    /* ── Button ── */
    .btn-login {
      width: 100%;
      padding: .75rem;
      background: var(--brand);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-family: 'DM Sans', sans-serif;
      font-size: .95rem;
      font-weight: 500;
      letter-spacing: .02em;
      cursor: pointer;
      transition: background .15s, transform .1s;
      margin-top: .25rem;
    }

    .btn-login:hover  { background: var(--brand-mid); }
    .btn-login:active { transform: scale(.99); }

    /* ── Accent line ── */
    .accent-line {
      height: 3px;
      border-radius: 2px;
      background: linear-gradient(90deg, var(--brand) 0%, var(--accent) 100%);
      margin-bottom: 1.75rem;
    }

    /* ── Footer ── */
    .login-footer {
      text-align: center;
      margin-top: 1.5rem;
      font-size: .78rem;
      color: var(--muted);
    }
  </style>
</head>
<body>
  <div class="login-wrap">

    <div class="login-header">
      <p class="school-name">Colegio De Sta. Teresa De Avila</p>
      <h1>Courseware</h1>
      <p class="subtitle">Course &amp; Schedule Management System</p>
    </div>

    <div class="login-card">
      <div class="accent-line"></div>

      <?php if ($error !== ''): ?>
        <div class="alert-login" role="alert"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php" novalidate>

        <div class="mb-field">
          <label class="form-label" for="username">Username</label>
          <input
            type="text"
            id="username"
            name="username"
            class="form-control"
            placeholder="Enter your username"
            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
            autocomplete="username"
            required
            autofocus
          >
        </div>

        <div class="mb-field">
          <label class="form-label" for="password">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            class="form-control"
            placeholder="Enter your password"
            autocomplete="current-password"
            required
          >
        </div>

        <button type="submit" class="btn-login">Sign in</button>

      </form>
    </div>

    <p class="login-footer">
      School of Information Technology &mdash; ITELECT Project &copy; <?= date('Y') ?>
    </p>

  </div>
</body>
</html>