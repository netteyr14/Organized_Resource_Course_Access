<?php
// ================================================================
//   DELETE.PHP — Copy this into modules/users/delete.php
// ================================================================

session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit; }
if ($_SESSION['role'] !== 'admin') { header('Location: ../../index.php'); exit; }

require_once '../../config/db.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id === 0) { header('Location: index.php'); exit; }

// Prevent self-deletion
if ($id === $_SESSION['user_id']) {
    header('Location: index.php?error=You+cannot+delete+your+own+account.');
    exit;
}

// Confirm record exists
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ?");
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    header('Location: index.php?error=User+not+found.');
    exit;
}

// Safe to delete
$del = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
$del->execute([$id]);

header('Location: index.php?success=deleted');
exit;