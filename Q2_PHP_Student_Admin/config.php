<?php
declare(strict_types=1);
session_start();

$host = 'localhost';
$dbName = 'student_admin';
$dbUser = 'root';
$dbPassword = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $exception) {
    http_response_code(500);
    exit('Database connection failed. Check the database configuration.');
}

$uploadDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
if (!is_dir($uploadDirectory)) {
    mkdir($uploadDirectory, 0750, true);
}

function escapeHtml(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function redirectWithMessage(string $message, string $type = 'success'): never {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
    header('Location: index.php');
    exit;
}
function saveUploadedFile(string $key, string $category, array $allowedMimes, int $maxBytes, string $directory): ?array {
    if (!isset($_FILES[$key]) || $_FILES[$key]['error'] === UPLOAD_ERR_NO_FILE) return null;
    $file = $_FILES[$key];
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('The upload did not complete successfully.');
    if (!is_uploaded_file($file['tmp_name'])) throw new RuntimeException('Invalid uploaded file.');
    if ($file['size'] < 1 || $file['size'] > $maxBytes) throw new RuntimeException('The selected file is empty or exceeds the size limit.');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowedMimes, true)) throw new RuntimeException('The selected file type is not permitted.');
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];
    $storedName = bin2hex(random_bytes(18)) . '.' . $extensions[$mime];
    $destination = $directory . DIRECTORY_SEPARATOR . $storedName;
    if (file_exists($destination)) throw new RuntimeException('A file name collision occurred. Please retry.');
    if (!move_uploaded_file($file['tmp_name'], $destination)) throw new RuntimeException('Could not store the uploaded file.');
    return [
        'original_name' => basename($file['name']),
        'stored_name' => $storedName,
        'mime_type' => $mime,
        'file_size' => (int)$file['size'],
        'category' => $category
    ];
}
function removeStoredFile(string $storedName, string $directory): void {
    if (!preg_match('/^[a-f0-9]{36}\.(jpg|png|pdf)$/', $storedName)) return;
    $path = $directory . DIRECTORY_SEPARATOR . $storedName;
    if (is_file($path)) unlink($path);
}
