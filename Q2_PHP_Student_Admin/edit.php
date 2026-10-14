<?php
require_once __DIR__ . '/config.php';
$_SESSION['csrf'] ??= bin2hex(random_bytes(24));
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([$id ?: 0]);
$student = $stmt->fetch();
if (!$student) { http_response_code(404); exit('Student record not found.'); }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) $errors[] = 'Invalid form token. Reload and try again.';
    $name = trim($_POST['name'] ?? '');
    $roll = strtoupper(trim($_POST['roll_no'] ?? ''));
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');
    if ($name === '' || $roll === '' || $email === '' || $course === '') $errors[] = 'Complete all required fields.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    $newFiles = [];
    if (!$errors) {
        try {
            $photo = saveUploadedFile('profile_image', 'profile_image', ['image/jpeg','image/png'], 2*1024*1024, $uploadDirectory);
            $document = saveUploadedFile('document', 'document', ['application/pdf','image/jpeg','image/png'], 5*1024*1024, $uploadDirectory);
            foreach ([$photo, $document] as $file) if ($file) $newFiles[] = $file;
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('UPDATE students SET name=?, roll_no=?, email=?, course=? WHERE id=?');
            $stmt->execute([$name, $roll, $email, $course, $id]);
            foreach ($newFiles as $file) {
                $stmt = $pdo->prepare('INSERT INTO student_files (student_id, original_name, stored_name, mime_type, file_size, category) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$id, $file['original_name'], $file['stored_name'], $file['mime_type'], $file['file_size'], $file['category']]);
            }
            $pdo->commit();
            redirectWithMessage('Student record updated. New files were added without overwriting existing files.');
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            foreach ($newFiles as $file) removeStoredFile($file['stored_name'], $uploadDirectory);
            $errors[] = $exception instanceof PDOException ? 'Roll number or email is already used by another student.' : $exception->getMessage();
        }
    }
    $student = array_merge($student, ['name'=>$name ?? $student['name'], 'roll_no'=>$roll ?? $student['roll_no'], 'email'=>$email ?? $student['email'], 'course'=>$course ?? $student['course']]);
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Edit student</title><link rel="stylesheet" href="style.css"></head><body>
<header class="topbar"><div><span class="brand">SA</span><strong>Student Admin Portal</strong></div><a href="index.php">← Directory</a></header>
<main class="container narrow"><p class="eyebrow">UPDATE RECORD</p><h1>Edit student</h1>
<?php if ($errors): ?><div class="notice error"><ul><?php foreach ($errors as $error): ?><li><?= escapeHtml($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form class="panel student-form" method="post" enctype="multipart/form-data"><input type="hidden" name="id" value="<?= (int)$id ?>"><input type="hidden" name="csrf" value="<?= escapeHtml($_SESSION['csrf']) ?>">
<label>Full name *<input name="name" value="<?= escapeHtml($student['name']) ?>" required></label>
<label>Roll number *<input name="roll_no" value="<?= escapeHtml($student['roll_no']) ?>" required></label>
<label>Email *<input type="email" name="email" value="<?= escapeHtml($student['email']) ?>" required></label>
<label>Course *<select name="course" required><option value="">Choose course</option><?php foreach (['B.E. Computer Science and Engineering','B.Tech Information Technology','B.E. Electronics and Communication Engineering','B.Tech Artificial Intelligence and Data Science'] as $option): ?><option <?= $student['course'] === $option ? 'selected' : '' ?>><?= escapeHtml($option) ?></option><?php endforeach; ?></select></label>
<label>Add / replace with a new profile image (JPG/PNG, max 2 MB)<input type="file" name="profile_image" accept=".jpg,.jpeg,.png,image/jpeg,image/png"></label>
<label>Add another document (PDF/JPG/PNG, max 5 MB)<input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"></label>
<p class="hint">New uploads receive unique stored names. Existing files are retained in the record.</p>
<div class="form-actions"><button class="button" type="submit">Save changes</button><a class="button secondary" href="view.php?id=<?= (int)$id ?>">Cancel</a></div>
</form></main><footer>Akshay A · 24CS015</footer></body></html>
