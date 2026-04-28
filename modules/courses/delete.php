<?php
// ============================================================
//  modules/courses/delete.php  —  Delete a course
// ============================================================
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit; }

require_once '../../config/db.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id === 0) { header('Location: index.php'); exit; }

// Confirm the record exists
$stmt = $pdo->prepare("SELECT course_id FROM courses WHERE course_id = ?");
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    header('Location: index.php?error=Course+not+found.');
    exit;
}

// Guard: check if this course is referenced by any schedule
$ref = $pdo->prepare("SELECT COUNT(*) FROM schedules WHERE course_id = ?");
$ref->execute([$id]);
if ($ref->fetchColumn() > 0) {
    header('Location: index.php?error=Cannot+delete+this+course+because+it+has+existing+schedules.+Remove+those+schedules+first.');
    exit;
}

// Safe to delete
$del = $pdo->prepare("DELETE FROM courses WHERE course_id = ?");
$del->execute([$id]);

header('Location: index.php?success=deleted');
exit;