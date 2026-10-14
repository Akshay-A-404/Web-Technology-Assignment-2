<?php
require_once __DIR__ . '/config.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([$id ?: 0]);
$student = $stmt->fetch();
if (!$student) { http_response_code(404); exit('Student record not found.'); }
$stmt = $pdo->prepare('SELECT * FROM student_files WHERE student_id = ? ORDER BY uploaded_at DESC');
$stmt->execute([$student['id']]);
$files = $stmt->fetchAll();
$photo = null;
foreach ($files as $file) if ($file['category'] === 'profile_image') { $photo = $file; break; }
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Student profile</title><link rel="stylesheet" href="style.css"></head><body>
<header class="topbar"><div><span class="brand">SA</span><strong>Student Admin Portal</strong></div><a href="index.php">← Directory</a></header>
<main class="container"><p class="eyebrow">STUDENT PROFILE</p><h1><?= escapeHtml($student['name']) ?></h1>
<section class="panel profile">
<?php if ($photo): ?><img class="profile-photo" src="file.php?name=<?= rawurlencode($photo['stored_name']) ?>" alt="Profile photo"><?php else: ?><div class="profile-photo placeholder"><?= escapeHtml(strtoupper(substr($student['name'],0,1))) ?></div><?php endif; ?>
<div class="details"><p><span>Roll number</span><strong><?= escapeHtml($student['roll_no']) ?></strong></p><p><span>Email</span><strong><?= escapeHtml($student['email']) ?></strong></p><p><span>Course</span><strong><?= escapeHtml($student['course']) ?></strong></p><p><span>Registered</span><strong><?= escapeHtml($student['created_at']) ?></strong></p><div class="form-actions"><a class="button" href="edit.php?id=<?= (int)$student['id'] ?>">Edit profile</a><a class="button secondary" href="index.php">Back</a></div></div>
</section>
<section class="panel"><h2>Uploaded files</h2><?php if (!$files): ?><p>No files have been uploaded.</p><?php else: ?><div class="table-wrap"><table><thead><tr><th>Category</th><th>Original filename</th><th>Type</th><th>Size</th><th>Uploaded</th><th>Action</th></tr></thead><tbody>
<?php foreach ($files as $file): ?><tr><td><?= escapeHtml(str_replace('_',' ',ucfirst($file['category']))) ?></td><td><?= escapeHtml($file['original_name']) ?></td><td><?= escapeHtml($file['mime_type']) ?></td><td><?= number_format($file['file_size']/1024,1) ?> KB</td><td><?= escapeHtml($file['uploaded_at']) ?></td><td><a href="file.php?name=<?= rawurlencode($file['stored_name']) ?>&download=1">Download</a></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></section></main><footer>Akshay A · 24CS015 · III-CSE-A</footer></body></html>
