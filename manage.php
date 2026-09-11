<?php
session_start();
require_once __DIR__ . DIRECTORY_SEPARATOR . 'db.php';
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'assistant'], true)) {
    header('Location: index.php');
    exit;
}

$user = $_SESSION['user'];
$isAdmin = $user['role'] === 'admin';
$database = db();
$contentRows = $database->query('SELECT type, title, details, image_path, video_path FROM content ORDER BY id DESC')->fetchAll();
$content = ['announcements' => [], 'courses' => [], 'consultancy' => []];
foreach ($contentRows as $item) {
    $bucket = $item['type'] === 'course' ? 'courses' : ($item['type'] === 'consultancy' ? 'consultancy' : 'announcements');
    $content[$bucket][] = $item['type'] === 'course' ? ['title' => $item['title'], 'details' => $item['details']] : ['title' => $item['title'], 'body' => $item['details']];
}
$requests = $database->query('SELECT * FROM requests ORDER BY created_at DESC')->fetchAll();
$projects = $database->query('SELECT * FROM projects ORDER BY created_at DESC')->fetchAll();
$publications = $database->query('SELECT * FROM publications ORDER BY created_at DESC')->fetchAll();
$events = $database->query('SELECT * FROM events ORDER BY event_date ASC')->fetchAll();
$donations = $database->query('SELECT * FROM donations ORDER BY created_at DESC')->fetchAll();
$users = $isAdmin ? $database->query('SELECT name, username, role, created_at FROM users ORDER BY id DESC')->fetchAll() : [];
$message = '';
if (isset($_GET['published'])) {
    $message = 'Published successfully. Clients can now see this on their workspace.';
}
if (isset($_GET['updated'])) {
    $message = 'Client request status updated.';
}
if (isset($_GET['deleted'])) {
    $message = 'Request record deleted.';
}
if (isset($_GET['project_saved'])) {
    $message = 'Project added to the BRD portfolio.';
}
if (isset($_GET['publication_saved'])) {
    $message = 'Publication added to the BRD library.';
}
if (isset($_GET['event_saved'])) {
    $message = 'Event added to the BRD calendar.';
}
if (isset($_GET['donation_saved'])) {
    $message = 'Support record saved successfully.';
}
if (isset($_GET['user_saved'])) {
    $message = 'Portal user created successfully.';
}
if (isset($_GET['user_error'])) {
    $message = 'User could not be created. Use a unique username and a password of at least 8 characters.';
}

function uploadMedia($field, $allowedExtensions, $allowedMimeTypes)
{
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK || $_FILES[$field]['size'] > 50 * 1024 * 1024) {
        return null;
    }
    $extension = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        return null;
    }
    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES[$field]['tmp_name']);
    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        return null;
    }
    $directory = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
    $filename = bin2hex(random_bytes(12)) . '.' . $extension;
    $destination = $directory . DIRECTORY_SEPARATOR . $filename;
    return move_uploaded_file($_FILES[$field]['tmp_name'], $destination) ? 'uploads/' . $filename : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'publish') {
        $type = in_array($_POST['content_type'] ?? '', ['announcements', 'courses', 'consultancy'], true) ? $_POST['content_type'] : 'announcements';
        $title = trim((string) ($_POST['title'] ?? ''));
        $details = trim((string) ($_POST['details'] ?? ''));
        if ($title !== '' && $details !== '') {
            $statement = $database->prepare('INSERT INTO content (type, title, details, published_by, created_at) VALUES (?, ?, ?, ?, ?)');
            $imagePath = uploadMedia('image', ['jpg', 'jpeg', 'png', 'webp', 'gif'], ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
            $videoPath = uploadMedia('video', ['mp4', 'webm', 'ogg'], ['video/mp4', 'video/webm', 'video/ogg']);
            $statement = $database->prepare('INSERT INTO content (type, title, details, image_path, video_path, published_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $contentType = $type === 'courses' ? 'course' : ($type === 'consultancy' ? 'consultancy' : 'announcement');
            $statement->execute([$contentType, $title, $details, $imagePath, $videoPath, $user['username'], date('Y-m-d H:i:s')]);
            header('Location: manage.php?published=1');
            exit;
        }
    }
    if ($action === 'update_request' && $isAdmin) {
        $requestId = $_POST['request_id'] ?? '';
        $statement = $database->prepare('UPDATE requests SET status = ?, payment_status = ? WHERE id = ?');
        $statement->execute([$_POST['status'] ?? 'Submitted', $_POST['payment_status'] ?? 'Pending', $requestId]);
        header('Location: manage.php?updated=1');
        exit;
    }
    if ($action === 'confirm_request' && $isAdmin) {
        $statement = $database->prepare("UPDATE requests SET status = 'Confirmed' WHERE id = ?");
        $statement->execute([$_POST['request_id'] ?? '']);
        header('Location: manage.php?updated=1');
        exit;
    }
    if ($action === 'delete_request' && $isAdmin) {
        $statement = $database->prepare('DELETE FROM requests WHERE id = ?');
        $statement->execute([$_POST['request_id'] ?? '']);
        header('Location: manage.php?deleted=1');
        exit;
    }
    if ($action === 'add_project' && $isAdmin) {
        $title = trim((string) ($_POST['project_title'] ?? ''));
        $location = trim((string) ($_POST['project_location'] ?? ''));
        $status = trim((string) ($_POST['project_status'] ?? 'Active'));
        $details = trim((string) ($_POST['project_details'] ?? ''));
        if ($title !== '' && $location !== '' && $details !== '') {
            $statement = $database->prepare('INSERT INTO projects (title, location, status, details, created_at) VALUES (?, ?, ?, ?, ?)');
            $statement->execute([$title, $location, $status !== '' ? $status : 'Active', $details, date('Y-m-d H:i:s')]);
            header('Location: manage.php?project_saved=1');
            exit;
        }
    }
    if ($action === 'add_publication' && $isAdmin) {
        $title = trim((string) ($_POST['publication_title'] ?? ''));
        $type = trim((string) ($_POST['publication_type'] ?? 'Report'));
        $details = trim((string) ($_POST['publication_details'] ?? ''));
        if ($title !== '' && $details !== '') {
            $statement = $database->prepare('INSERT INTO publications (title, type, details, created_at) VALUES (?, ?, ?, ?)');
            $statement->execute([$title, $type !== '' ? $type : 'Report', $details, date('Y-m-d H:i:s')]);
            header('Location: manage.php?publication_saved=1');
            exit;
        }
    }
    if ($action === 'add_event' && $isAdmin) {
        $title = trim((string) ($_POST['event_title'] ?? ''));
        $date = trim((string) ($_POST['event_date'] ?? ''));
        $location = trim((string) ($_POST['event_location'] ?? ''));
        $details = trim((string) ($_POST['event_details'] ?? ''));
        $dateIsValid = DateTime::createFromFormat('Y-m-d', $date);
        if ($title !== '' && $date !== '' && $dateIsValid && $dateIsValid->format('Y-m-d') === $date && $location !== '' && $details !== '') {
            $statement = $database->prepare('INSERT INTO events (title, event_date, location, details, created_at) VALUES (?, ?, ?, ?, ?)');
            $statement->execute([$title, $date, $location, $details, date('Y-m-d H:i:s')]);
            header('Location: manage.php?event_saved=1');
            exit;
        }
    }
    if ($action === 'add_donation' && $isAdmin) {
        $supporter = trim((string) ($_POST['supporter_name'] ?? ''));
        $type = trim((string) ($_POST['support_type'] ?? 'Donation'));
        $amount = trim((string) ($_POST['amount'] ?? ''));
        $note = trim((string) ($_POST['donation_note'] ?? ''));
        if ($supporter !== '' && $amount !== '' && $note !== '') {
            $statement = $database->prepare('INSERT INTO donations (supporter_name, support_type, amount, note, created_at) VALUES (?, ?, ?, ?, ?)');
            $statement->execute([$supporter, $type !== '' ? $type : 'Donation', $amount, $note, date('Y-m-d H:i:s')]);
            header('Location: manage.php?donation_saved=1');
            exit;
        }
    }
    if ($action === 'add_user' && $isAdmin) {
        $name = trim((string) ($_POST['user_name'] ?? ''));
        $username = trim((string) ($_POST['user_username'] ?? ''));
        $role = $_POST['user_role'] ?? 'client';
        $password = (string) ($_POST['user_password'] ?? '');
        if ($name !== '' && preg_match('/^[a-zA-Z0-9._-]{3,100}$/', $username) && in_array($role, ['client', 'assistant'], true) && strlen($password) >= 8) {
            $existing = $database->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
            $existing->execute([$username]);
            if (!$existing->fetch()) {
                $statement = $database->prepare('INSERT INTO users (name, username, role, password_hash, created_at) VALUES (?, ?, ?, ?, ?)');
                $statement->execute([$name, $username, $role, password_hash($password, PASSWORD_DEFAULT), date('Y-m-d H:i:s')]);
                header('Location: manage.php?user_saved=1');
                exit;
            }
        }
        header('Location: manage.php?user_error=1');
        exit;
    }
}
$esc = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BRD operations | <?= $esc(ucfirst($user['role'])) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"><link rel="stylesheet" href="styles.css">
  <style>
    .ops-shell{min-height:100vh;background:var(--cream)}.ops-header,.ops-main,.ops-footer{max-width:1240px;margin:auto}.ops-header{padding:26px 32px;border-bottom:1px solid #d9dcd7;display:flex;justify-content:space-between;align-items:center}.ops-header .brand-mark{transform:scale(.84);transform-origin:left top;margin-bottom:-5px}.ops-header .brand-subtitle{font-size:8px}.ops-user{display:flex;gap:18px;align-items:center;color:var(--muted);font-size:13px}.ops-main{padding:56px 32px 80px}.ops-hero{display:flex;justify-content:space-between;align-items:end;gap:30px;margin-bottom:34px}.ops-hero h1{margin:12px 0;color:var(--navy);font:600 clamp(52px,7vw,84px)/.88 "Barlow Condensed",sans-serif;text-transform:uppercase}.ops-hero p:not(.eyebrow){max-width:580px;color:var(--muted);line-height:1.7}.ops-message{padding:15px 20px;background:#e6f0e8;border-left:4px solid var(--green);color:var(--green);font-size:13px}.ops-grid{display:grid;grid-template-columns:1fr 1fr;gap:22px}.ops-panel{padding:30px;background:#fff;border-top:4px solid var(--navy)}.ops-panel h2{margin:0 0 20px;color:var(--navy);font:600 36px/1 "Barlow Condensed",sans-serif;text-transform:uppercase}.ops-form label{display:block;margin:14px 0;font-size:12px;font-weight:700}.ops-form input,.ops-form select,.ops-form textarea{display:block;width:100%;margin-top:6px;padding:12px;border:1px solid #c9ceca;background:#fff;font:13px "DM Sans",sans-serif}.ops-form textarea{min-height:90px;resize:vertical}.ops-form button{border:0;cursor:pointer;margin-top:10px}.ops-form button span{float:right}.upload-note{margin:12px 0;color:var(--muted);font-size:11px;line-height:1.5}.ops-table{margin-top:24px;border-top:1px solid #d9dcd7}.ops-row{display:grid;grid-template-columns:1fr 1fr 1.4fr;gap:14px;padding:15px 0;border-bottom:1px solid #d9dcd7;font-size:12px;align-items:center}.ops-row strong{color:var(--navy)}.ops-row form{display:flex;gap:7px}.ops-row select,.ops-row button{padding:7px;border:1px solid #c9ceca;background:#fff;font:11px "DM Sans",sans-serif}.ops-row button{background:var(--navy);color:#fff;cursor:pointer}.ops-footer{padding:0 32px 28px;color:var(--muted);font-size:12px}.ops-footer a{color:var(--navy);font-weight:700;text-decoration:none}@media(max-width:760px){.ops-header{padding:22px 20px}.ops-user span{display:none}.ops-main{padding:42px 20px 60px}.ops-hero{display:block}.ops-grid{display:block}.ops-panel+ .ops-panel{margin-top:22px}.ops-row{grid-template-columns:1fr}.ops-row form{flex-wrap:wrap}.ops-footer{padding:0 20px 22px}}
  </style>
</head>
<body><div class="topline"></div><div class="ops-shell">
    <header class="ops-header"><a class="brand" href="index.php" aria-label="BRD home"><span class="brand-mark"><b>B</b><b class="torch">R<span>●</span></b><b>D<i>••</i></b></span><span class="brand-subtitle">Building Resilience to Disasters</span></a><div class="ops-user"><span><?= $esc($user['name']) ?> · <?= $esc(ucfirst($user['role'])) ?></span><?php if ($isAdmin): ?><a class="text-link" href="request_admin.php">Request desk <span>→</span></a><?php endif; ?><a class="text-link" href="profile.php">Profile <span>→</span></a><a class="text-link" href="logout.php">Sign out <span>↗</span></a></div></header>
  <main class="ops-main"><section class="ops-hero"><div><p class="eyebrow">BRD operations</p><h1>Publish useful work.</h1><p>Keep clients informed with current training and research updates. Administrators can also oversee applications and payments.</p></div></section><p class="ops-message"><?= $esc($message) ?></p>
    <div class="ops-grid"><section class="ops-panel"><h2>Publish to clients</h2><form class="ops-form" method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="publish"><label>Content type<select name="content_type"><option value="announcements">Announcement</option><option value="courses">Training course</option><option value="consultancy">Consultancy service</option></select></label><label>Title<input name="title" required placeholder="Example: Applications open for..." /></label><label>Description<textarea name="details" required placeholder="Include dates, duration, requirements, or next steps"></textarea></label><label>Course image<input name="image" type="file" accept="image/jpeg,image/png,image/webp,image/gif"></label><label>Course video<input name="video" type="file" accept="video/mp4,video/webm,video/ogg"></label><p class="upload-note">Images and videos must be under 50 MB. Supported video formats: MP4, WebM, OGG.</p><button class="button button-red" type="submit">Publish update <span>→</span></button></form></section>
            <section class="ops-panel"><h2>Published content</h2><div class="ops-table"><?php foreach (array_merge($content['announcements'], $content['courses'], $content['consultancy']) as $item): ?><div class="ops-row"><strong><?= $esc($item['title'] ?? '') ?></strong><span><?= isset($item['body']) ? 'Announcement' : 'Training or consultancy' ?></span><span>Visible to clients</span></div><?php endforeach; ?></div></section></div>
        <?php if ($isAdmin): ?><section class="ops-panel" style="margin-top:22px"><h2>Add project to portfolio</h2><form class="ops-form" method="post"><input type="hidden" name="action" value="add_project"><label>Project title<input name="project_title" required placeholder="Example: Urban flood preparedness study" /></label><label>Location<input name="project_location" required placeholder="Dar es Salaam" /></label><label>Status<select name="project_status"><option>Active</option><option>In review</option><option>Planned</option><option>Complete</option></select></label><label>Project details<textarea name="project_details" required placeholder="Describe the project objective and expected impact"></textarea></label><button class="button button-red" type="submit">Save project <span>→</span></button></form><div class="ops-table" style="margin-top:18px"><?php foreach ($projects as $project): ?><div class="ops-row"><strong><?= $esc($project['title'] ?? '') ?></strong><span><?= $esc($project['location'] ?? '') ?></span><span><?= $esc($project['status'] ?? '') ?></span></div><?php endforeach; ?><?php if (!$projects): ?><p>No projects have been added yet.</p><?php endif; ?></div></section><section class="ops-panel" style="margin-top:22px"><h2>Add publication</h2><form class="ops-form" method="post"><input type="hidden" name="action" value="add_publication"><label>Publication title<input name="publication_title" required placeholder="Research summary on climate resilience" /></label><label>Type<input name="publication_type" value="Report" required /></label><label>Details<textarea name="publication_details" required placeholder="Provide the publication summary or key insights"></textarea></label><button class="button button-red" type="submit">Save publication <span>→</span></button></form><div class="ops-table" style="margin-top:18px"><?php foreach ($publications as $publication): ?><div class="ops-row"><strong><?= $esc($publication['title'] ?? '') ?></strong><span><?= $esc($publication['type'] ?? '') ?></span><span><?= $esc($publication['details'] ?? '') ?></span></div><?php endforeach; ?><?php if (!$publications): ?><p>No publications have been added yet.</p><?php endif; ?></div></section><section class="ops-panel" style="margin-top:22px"><h2>Add BRD event</h2><form class="ops-form" method="post"><input type="hidden" name="action" value="add_event"><label>Event title<input name="event_title" required placeholder="Community resilience planning clinic" /></label><label>Event date<input type="date" name="event_date" required /></label><label>Location<input name="event_location" required placeholder="Dar es Salaam" /></label><label>Event details<textarea name="event_details" required placeholder="Describe the event purpose, audience and next steps"></textarea></label><button class="button button-red" type="submit">Save event <span>→</span></button></form><div class="ops-table" style="margin-top:18px"><?php foreach ($events as $event): ?><div class="ops-row"><strong><?= $esc($event['title'] ?? '') ?></strong><span><?= $esc(date('d M Y', strtotime($event['event_date'] ?? 'now'))) ?></span><span><?= $esc($event['location'] ?? '') ?></span></div><?php endforeach; ?><?php if (!$events): ?><p>No events have been added yet.</p><?php endif; ?></div></section><section class="ops-panel" style="margin-top:22px"><h2>Record support or partnership</h2><form class="ops-form" method="post"><input type="hidden" name="action" value="add_donation"><label>Supporter or partner<input name="supporter_name" required placeholder="Tanzania Resilience Fund" /></label><label>Support type<select name="support_type"><option>Donation</option><option>Partnership</option><option>In-kind support</option><option>Grant</option></select></label><label>Amount<input name="amount" required placeholder="TZS 2,500,000" /></label><label>Note<textarea name="donation_note" required placeholder="Explain what the support is for and how it helps BRD"></textarea></label><button class="button button-red" type="submit">Save support record <span>→</span></button></form><div class="ops-table" style="margin-top:18px"><?php foreach ($donations as $donation): ?><div class="ops-row"><strong><?= $esc($donation['supporter_name'] ?? '') ?></strong><span><?= $esc($donation['support_type'] ?? '') ?></span><span><?= $esc($donation['amount'] ?? '') ?></span></div><?php endforeach; ?><?php if (!$donations): ?><p>No support records have been added yet.</p><?php endif; ?></div></section><section class="ops-panel" style="margin-top:22px"><h2>Admission and payment oversight</h2><div class="ops-table"><?php foreach ($requests as $request): ?><div class="ops-row"><strong><?= $esc($request['name'] ?? '') ?></strong><span><?= $esc(ucfirst($request['type'] ?? 'request')) ?></span><form method="post"><input type="hidden" name="action" value="update_request"><input type="hidden" name="request_id" value="<?= $esc($request['id'] ?? '') ?>"><select name="status"><option <?= ($request['status'] ?? '') === 'Submitted' ? 'selected' : '' ?>>Submitted</option><option <?= ($request['status'] ?? '') === 'Under review' ? 'selected' : '' ?>>Under review</option><option <?= ($request['status'] ?? '') === 'Approved' ? 'selected' : '' ?>>Approved</option><option <?= ($request['status'] ?? '') === 'Rejected' ? 'selected' : '' ?>>Rejected</option></select><select name="payment_status"><option <?= ($request['payment_status'] ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option><option <?= ($request['payment_status'] ?? '') === 'Paid' ? 'selected' : '' ?>>Paid</option><option <?= ($request['payment_status'] ?? '') === 'Not applicable' ? 'selected' : '' ?>>Not applicable</option></select><button type="submit">Update</button></form></div><?php endforeach; ?><?php if (!$requests): ?><p>No client requests have been submitted yet.</p><?php endif; ?></div></section><section class="ops-panel" style="margin-top:22px"><h2>Registered users</h2><div class="ops-table"><?php foreach ($users as $registeredUser): ?><div class="ops-row"><strong><?= $esc($registeredUser['name']) ?></strong><span><?= $esc($registeredUser['username']) ?></span><span><?= $esc(ucfirst($registeredUser['role'])) ?> · Joined <?= $esc(date('M j, Y', strtotime($registeredUser['created_at']))) ?></span></div><?php endforeach; ?><?php if (!$users): ?><p>No registered client or assistant accounts yet.</p><?php endif; ?></div></section><?php endif; ?>
    <?php if ($isAdmin): ?><section class="ops-panel" style="margin-top:22px"><h2>Create portal user</h2><form class="ops-form" method="post"><input type="hidden" name="action" value="add_user"><label>Full name<input name="user_name" required placeholder="Example: Asha Mollel" /></label><label>Username<input name="user_username" required pattern="[A-Za-z0-9._-]{3,100}" placeholder="asha.mollel" /></label><label>Role<select name="user_role"><option value="client">Client</option><option value="assistant">Assistant</option></select></label><label>Temporary password<input name="user_password" type="password" minlength="8" required placeholder="At least 8 characters" /></label><button class="button button-red" type="submit">Create user <span>→</span></button></form><div class="ops-table" style="margin-top:18px"><?php foreach ($users as $registeredUser): ?><div class="ops-row"><strong><?= $esc($registeredUser['name']) ?></strong><span><?= $esc($registeredUser['username']) ?></span><span><?= $esc(ucfirst($registeredUser['role'])) ?></span></div><?php endforeach; ?></div></section><?php endif; ?>
    </main><footer class="ops-footer"><p>BRD Research Consulting Centre operations workspace</p><a href="dashboard.php">Return to dashboard ↑</a></footer>
</div></body></html>
