<?php
require_once __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed.'); }
if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(403); exit('Invalid form token.'); }
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) redirectWithMessage('Invalid student identifier.', 'error');
$stmt = $pdo->prepare('SELECT stored_name FROM student_files WHERE student_id = ?');
$stmt->execute([$id]);
$storedNames = $stmt->fetchAll(PDO::FETCH_COLUMN);
$stmt = $pdo->prepare('DELETE FROM students WHERE id = ?');
$stmt->execute([$id]);
foreach ($storedNames as $storedName) removeStoredFile($storedName, $uploadDirectory);
redirectWithMessage($stmt->rowCount() ? 'Student record deleted.' : 'Student record was not found.', $stmt->rowCount() ? 'success' : 'error');
