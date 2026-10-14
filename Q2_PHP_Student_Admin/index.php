<?php
require_once __DIR__ . '/config.php';
$students = $pdo->query(
    "SELECT s.*, f.stored_name AS photo FROM students s
     LEFT JOIN student_files f ON f.student_id=s.id AND f.category='profile_image'
     ORDER BY s.id DESC"
)->fetchAll();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Admin</title><link rel="stylesheet" href="style.css"></head><body>
<header class="topbar"><div><span class="brand">SA</span><strong>Student Admin Portal</strong></div><a class="button" href="add.php">+ Add student</a></header>
<main class="container">
  <div class="page-title"><div><p class="eyebrow">ADMINISTRATION</p><h1>Student records</h1><p>Manage student information and uploaded documents.</p></div><span class="count"><?= count($students) ?> records</span></div>
  <?php if ($flash): ?><div class="notice <?= escapeHtml($flash['type']) ?>"><?= escapeHtml($flash['message']) ?></div><?php endif; ?>
  <section class="panel"><div class="panel-heading"><h2>Directory</h2><span>Student list</span></div>
  <?php if (!$students): ?><p class="empty">No student records yet. Add a student to get started.</p>
  <?php else: ?><div class="table-wrap"><table><thead><tr><th>Photo</th><th>Roll no.</th><th>Name</th><th>Course</th><th>Email</th><th>Actions</th></tr></thead><tbody>
  <?php foreach ($students as $student): ?><tr>
    <td><?php if ($student['photo'] && is_file($uploadDirectory . '/' . $student['photo'])): ?><img class="avatar" src="file.php?name=<?= rawurlencode($student['photo']) ?>" alt="Profile photo"><?php else: ?><span class="avatar placeholder"><?= escapeHtml(strtoupper(substr($student['name'],0,1))) ?></span><?php endif; ?></td>
    <td><strong><?= escapeHtml($student['roll_no']) ?></strong></td><td><?= escapeHtml($student['name']) ?></td><td><?= escapeHtml($student['course']) ?></td><td><?= escapeHtml($student['email']) ?></td>
    <td class="actions"><a href="view.php?id=<?= (int)$student['id'] ?>">View</a><a href="edit.php?id=<?= (int)$student['id'] ?>">Edit</a><form method="post" action="delete.php" onsubmit="return confirm('Delete this student and their files?');"><input type="hidden" name="id" value="<?= (int)$student['id'] ?>"><input type="hidden" name="csrf" value="<?= escapeHtml($_SESSION['csrf'] ??= bin2hex(random_bytes(24))) ?>"><button class="link danger" type="submit">Delete</button></form></td>
  </tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
  </section>
</main><footer>Web Technology · Akshay A · 24CS015 · III-CSE-A</footer></body></html>
