<?php
require_once __DIR__ . '/config.php';
$_SESSION['csrf'] ??= bin2hex(random_bytes(24));
$errors = [];
$name = $roll = $email = $course = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) $errors[] = 'Invalid form token. Reload and try again.';
    $name = trim($_POST['name'] ?? '');
    $roll = strtoupper(trim($_POST['roll_no'] ?? ''));
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');
    if ($name === '' || $roll === '' || $email === '' || $course === '') $errors[] = 'Complete all required fields.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    $saved = [];
    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO students (name, roll_no, email, course) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $roll, $email, $course]);
            $studentId = (int)$pdo->lastInsertId();
            $photo = saveUploadedFile('profile_image', 'profile_image', ['image/jpeg','image/png'], 2*1024*1024, $uploadDirectory);
            $document = saveUploadedFile('document', 'document', ['application/pdf','image/jpeg','image/png'], 5*1024*1024, $uploadDirectory);
            foreach ([$photo, $document] as $file) {
                if ($file) {
                    $insert = $pdo->prepare('INSERT INTO student_files (student_id, original_name, stored_name, mime_type, file_size, category) VALUES (?, ?, ?, ?, ?, ?)');
                    $insert->execute([$studentId, $file['original_name'], $file['stored_name'], $file['mime_type'], $file['file_size'], $file['category']]);
                    $saved[] = $file['stored_name'];
                }
            }
            $pdo->commit();
            redirectWithMessage('Student record created successfully.');
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            foreach ($saved as $stored) removeStoredFile($stored, $uploadDirectory);
            $errors[] = $exception instanceof PDOException ? 'Roll number or email already exists, or the database rejected the record.' : $exception->getMessage();
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Add student</title><link rel="stylesheet" href="style.css"></head><body>
<header class="topbar"><div><span class="brand">SA</span><strong>Student Admin Portal</strong></div><a href="index.php">← Directory</a></header>
<main class="container narrow"><p class="eyebrow">NEW RECORD</p><h1>Add student</h1>
<?php if ($errors): ?><div class="notice error"><ul><?php foreach ($errors as $error): ?><li><?= escapeHtml($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form class="panel student-form" method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf" value="<?= escapeHtml($_SESSION['csrf']) ?>">
<label>Full name *<input name="name" value="<?= escapeHtml($name) ?>" required maxlength="100"></label>
<label>Roll number *<input name="roll_no" value="<?= escapeHtml($roll) ?>" required maxlength="20"></label>
<label>Email *<input type="email" name="email" value="<?= escapeHtml($email) ?>" required maxlength="150"></label>
<label>Course *<select name="course" required><option value="">Choose course</option><?php foreach (['B.E. Computer Science and Engineering','B.Tech Information Technology','B.E. Electronics and Communication Engineering','B.Tech Artificial Intelligence and Data Science'] as $option): ?><option <?= $course === $option ? 'selected' : '' ?>><?= escapeHtml($option) ?></option><?php endforeach; ?></select></label>
<label>Profile image (JPG/PNG, max 2 MB)<input type="file" name="profile_image" accept=".jpg,.jpeg,.png,image/jpeg,image/png"></label>
<label>Certificate / ID proof (PDF/JPG/PNG, max 5 MB)<input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"></label>
<div class="form-actions"><button class="button" type="submit">Save student</button><a class="button secondary" href="index.php">Cancel</a></div>
</form></main><footer>Akshay A · 24CS015</footer></body></html>
