<?php
require_once __DIR__ . '/../config.php';
require_admin();
$cats = ['web', 'networking', 'animation'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($a === 'add_project') {
        $cat = in_array($_POST['category'] ?? '', $cats, true) ? $_POST['category'] : 'web';
        $img = basename(trim((string)($_POST['image'] ?? '')));   // filename only, no paths
        db()->prepare('INSERT INTO projects (title, category, description, technology, image) VALUES (?,?,?,?,?)')
            ->execute([mb_substr(trim($_POST['title'] ?? ''), 0, 150), $cat, mb_substr(trim($_POST['description'] ?? ''), 0, 1000), mb_substr(trim($_POST['technology'] ?? ''), 0, 150), $img]);
    } elseif ($a === 'delete_project') {
        db()->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
    } elseif ($a === 'add_skill') {
        db()->prepare('INSERT INTO skills (icon, name, description) VALUES (?,?,?)')
            ->execute([mb_substr(trim($_POST['icon'] ?? ''), 0, 10), mb_substr(trim($_POST['name'] ?? ''), 0, 100), mb_substr(trim($_POST['description'] ?? ''), 0, 255)]);
    } elseif ($a === 'delete_skill') {
        db()->prepare('DELETE FROM skills WHERE id = ?')->execute([$id]);
    } elseif ($a === 'add_resume') {
        db()->prepare('INSERT INTO resume_items (label, title, description) VALUES (?,?,?)')
            ->execute([mb_substr(trim($_POST['label'] ?? ''), 0, 50), mb_substr(trim($_POST['title'] ?? ''), 0, 150), mb_substr(trim($_POST['description'] ?? ''), 0, 1000)]);
    } elseif ($a === 'delete_resume') {
        db()->prepare('DELETE FROM resume_items WHERE id = ?')->execute([$id]);
    } elseif ($a === 'delete_message') {
        db()->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
    }
    header('Location: index.php'); exit;
}
$projects = db()->query('SELECT * FROM projects ORDER BY id')->fetchAll();
$skills   = db()->query('SELECT * FROM skills ORDER BY id')->fetchAll();
$resume   = db()->query('SELECT * FROM resume_items ORDER BY id')->fetchAll();
$messages = db()->query('SELECT * FROM messages ORDER BY created_at DESC')->fetchAll();

function del_form(string $action, int $id): string {
    return '<form method="post" onsubmit="return confirm(\'Delete this item?\')">' . csrf_field() .
        '<input type="hidden" name="action" value="' . e($action) . '"><input type="hidden" name="id" value="' . $id . '">' .
        '<button class="danger" type="submit">Delete</button></form>';
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title><link rel="stylesheet" href="../style.css"><link rel="stylesheet" href="admin.css"></head>
<body><main class="admin-wrap">
<p><a href="../index.php">← View site</a> · <a href="logout.php">Log out</a></p>
<h1>Admin Dashboard</h1>

<h2>Messages (<?= count($messages) ?>)</h2>
<table><tr><th>Date</th><th>From</th><th>Message</th><th></th></tr>
<?php foreach ($messages as $m): ?>
<tr><td><?= e($m['created_at']) ?></td><td><?= e($m['name']) ?><br><small><?= e($m['email']) ?></small></td><td><?= nl2br(e($m['message'])) ?></td><td><?= del_form('delete_message', (int)$m['id']) ?></td></tr>
<?php endforeach; ?></table>

<h2>Projects</h2>
<table><tr><th>Title</th><th>Category</th><th>Image</th><th></th></tr>
<?php foreach ($projects as $p): ?>
<tr><td><?= e($p['title']) ?></td><td><?= e($p['category']) ?></td><td><?= e($p['image']) ?></td><td><?= del_form('delete_project', (int)$p['id']) ?></td></tr>
<?php endforeach; ?></table>
<form method="post" class="contact-form"><?= csrf_field() ?><input type="hidden" name="action" value="add_project">
<h3>Add project</h3>
<input name="title" placeholder="Title" required maxlength="150">
<select name="category"><?php foreach ($cats as $c): ?><option value="<?= e($c) ?>"><?= e(ucfirst($c)) ?></option><?php endforeach; ?></select>
<textarea name="description" placeholder="Description" rows="3" maxlength="1000" required></textarea>
<input name="technology" placeholder="Technology" maxlength="150" required>
<input name="image" placeholder="Image filename, e.g. project5.png" maxlength="100" required>
<button class="btn" type="submit">Add project</button></form>

<h2>Skills</h2>
<table><tr><th>Icon</th><th>Name</th><th>Description</th><th></th></tr>
<?php foreach ($skills as $s): ?>
<tr><td><?= e($s['icon']) ?></td><td><?= e($s['name']) ?></td><td><?= e($s['description']) ?></td><td><?= del_form('delete_skill', (int)$s['id']) ?></td></tr>
<?php endforeach; ?></table>
<form method="post" class="contact-form"><?= csrf_field() ?><input type="hidden" name="action" value="add_skill">
<h3>Add skill</h3>
<input name="icon" placeholder="Emoji icon" maxlength="10" required>
<input name="name" placeholder="Skill name" maxlength="100" required>
<input name="description" placeholder="Short description" maxlength="255" required>
<button class="btn" type="submit">Add skill</button></form>
<h2>Resume</h2>
<table><tr><th>Label</th><th>Title</th><th></th></tr>
<?php foreach ($resume as $r): ?>
<tr><td><?= e($r['label']) ?></td><td><?= e($r['title']) ?></td><td><?= del_form('delete_resume', (int)$r['id']) ?></td></tr>
<?php endforeach; ?></table>
<form method="post" class="contact-form"><?= csrf_field() ?><input type="hidden" name="action" value="add_resume">
<h3>Add resume item</h3>
<input name="label" placeholder="Label (e.g. Education)" maxlength="50" required>
<input name="title" placeholder="Title" maxlength="150" required>
<textarea name="description" placeholder="Description" rows="3" maxlength="1000" required></textarea>
<button class="btn" type="submit">Add resume item</button></form>
</main></body></html>
