<?php
// ============================================================
//  modules/sections/delete.php  —  Delete a section
// ============================================================
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit; }

require_once '../../config/db.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id === 0) { header('Location: index.php'); exit; }

// Confirm the record exists
$stmt = $pdo->prepare("SELECT section_id FROM sections WHERE section_id = ?");
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    header('Location: index.php?error=Section+not+found.');
    exit;
}

// Guard: check if this section is referenced by any schedule
$ref = $pdo->prepare("SELECT COUNT(*) FROM schedules WHERE section_id = ?");
$ref->execute([$id]);
if ($ref->fetchColumn() > 0) {
    header('Location: index.php?error=Cannot+delete+this+section+because+it+has+existing+schedules.+Remove+those+schedules+first.');
    exit;
}

// Safe to delete
$del = $pdo->prepare("DELETE FROM sections WHERE section_id = ?");
$del->execute([$id]);

header('Location: index.php?success=deleted');
exit;