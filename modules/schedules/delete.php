<?php
// ============================================================
//  modules/schedules/delete.php  —  Delete a schedule
// ============================================================
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit; }

require_once '../../config/db.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id === 0) { header('Location: index.php'); exit; }

// Confirm the record exists
$stmt = $pdo->prepare("SELECT schedule_id FROM schedules WHERE schedule_id = ?");
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    header('Location: index.php?error=Schedule+not+found.');
    exit;
}

// Safe to delete — schedules have no child tables depending on them
$del = $pdo->prepare("DELETE FROM schedules WHERE schedule_id = ?");
$del->execute([$id]);

header('Location: index.php?success=deleted');
exit;