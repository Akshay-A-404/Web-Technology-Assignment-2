<?php
require_once __DIR__ . '/config.php';
$name = $_GET['name'] ?? '';
if (!preg_match('/^[a-f0-9]{36}\.(jpg|png|pdf)$/', $name)) { http_response_code(400); exit('Invalid file name.'); }
$stmt = $pdo->prepare('SELECT mime_type, original_name FROM student_files WHERE stored_name = ?');
$stmt->execute([$name]);
$file = $stmt->fetch();
$path = $uploadDirectory . DIRECTORY_SEPARATOR . $name;
if (!$file || !is_file($path)) { http_response_code(404); exit('File not found.'); }
header('X-Content-Type-Options: nosniff');
header('Content-Type: ' . $file['mime_type']);
$disposition = isset($_GET['download']) ? 'attachment' : 'inline';
header('Content-Disposition: ' . $disposition . '; filename="' . rawurlencode(basename($file['original_name'])) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
