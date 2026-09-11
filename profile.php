<?php
session_start();
require_once __DIR__ . DIRECTORY_SEPARATOR . 'db.php';
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$user = $_SESSION['user'];
$database = db();
$message = '';
$error = '';
$profile = $database->prepare('SELECT name, username, role, email, phone, bio, photo_path FROM users WHERE username = ? LIMIT 1');
$profile->execute([$user['username']]);
$profile = $profile->fetch() ?: ['name' => $user['name'], 'username' => $user['username'], 'role' => $user['role'], 'email' => '', 'phone' => '', 'bio' => '', 'photo_path' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $bio = trim((string) ($_POST['bio'] ?? ''));
    $photoPath = $profile['photo_path'];
    if ($name === '' || ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))) {
        $error = 'Enter a name and a valid email address.';
    } elseif (!empty($_FILES['photo']['name'])) {
        $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $mime = $_FILES['photo']['error'] === UPLOAD_ERR_OK ? (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['photo']['tmp_name']) : '';
        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK || $_FILES['photo']['size'] > 5 * 1024 * 1024 || !isset($allowed[$extension]) || $allowed[$extension] !== $mime) {
            $error = 'Profile photos must be JPG, PNG, or WEBP files under 5 MB.';
        } else {
            $directory = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profiles';
            if (!is_dir($directory)) mkdir($directory, 0755, true);
            $filename = bin2hex(random_bytes(12)) . '.' . $extension;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $directory . DIRECTORY_SEPARATOR . $filename)) {
                $photoPath = 'uploads/profiles/' . $filename;
            }
        }
    }
    if ($error === '') {
        $statement = $database->prepare('UPDATE users SET name = ?, email = ?, phone = ?, bio = ?, photo_path = ? WHERE username = ?');
        $statement->execute([$name, $email !== '' ? $email : null, $phone !== '' ? $phone : null, $bio !== '' ? $bio : null, $photoPath, $user['username']]);
        $_SESSION['user']['name'] = $name;
        $profile['name'] = $name;
        $profile['email'] = $email;
        $profile['phone'] = $phone;
        $profile['bio'] = $bio;
        $profile['photo_path'] = $photoPath;
        $message = 'Profile updated successfully.';
    }
}
$esc = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>BRD profile</title><link rel="stylesheet" href="styles.css"><style>
.profile-shell{min-height:100vh;background:var(--cream)}.profile-header,.profile-main,.profile-footer{max-width:1000px;margin:auto}.profile-header{padding:26px 32px;border-bottom:1px solid #d9dcd7;display:flex;justify-content:space-between;align-items:center}.profile-main{padding:56px 32px 80px}.profile-grid{display:grid;grid-template-columns:280px 1fr;gap:22px}.profile-card{background:#fff;padding:28px;border-top:4px solid var(--green)}.profile-photo{width:150px;height:150px;border-radius:50%;object-fit:cover;background:var(--navy);display:grid;place-items:center;color:#fff;font:600 70px/1 'Barlow Condensed',sans-serif;margin:0 auto 20px}.profile-card h1{margin:0;color:var(--navy);font:600 48px/.9 'Barlow Condensed',sans-serif;text-transform:uppercase}.profile-card p{color:var(--muted);line-height:1.6}.profile-form label{display:block;margin:15px 0;font-size:12px;font-weight:700}.profile-form input,.profile-form textarea{display:block;width:100%;margin-top:7px;padding:12px;border:1px solid #c9ceca;font:13px 'DM Sans',sans-serif}.profile-form textarea{min-height:110px;resize:vertical}.profile-form button{border:0;cursor:pointer;margin-top:10px}.notice{padding:14px 18px;background:#e6f0e8;border-left:4px solid var(--green);color:var(--green);font-size:13px;margin-bottom:20px}.notice.error{background:#f5e5e3;border-color:var(--red);color:var(--red)}.profile-footer{padding:0 32px 28px;color:var(--muted);font-size:12px;display:flex;justify-content:space-between}.profile-footer a{color:var(--navy);font-weight:700;text-decoration:none}@media(max-width:700px){.profile-header,.profile-main,.profile-footer{padding-left:20px;padding-right:20px}.profile-grid{grid-template-columns:1fr}.profile-footer{display:block}}
</style></head><body><div class="topline"></div><div class="profile-shell"><header class="profile-header"><a class="brand" href="index.php"><span class="brand-mark"><b>B</b><b class="torch">R<span>●</span></b><b>D<i>••</i></b></span><span class="brand-subtitle">Building Resilience to Disasters</span></a><a class="text-link" href="dashboard.php">Back to workspace <span>↗</span></a></header><main class="profile-main"><p class="eyebrow">BRD profile</p><div class="profile-grid"><aside class="profile-card"><?php if (!empty($profile['photo_path'])): ?><img class="profile-photo" src="<?= $esc($profile['photo_path']) ?>" alt="Profile photo"><?php else: ?><div class="profile-photo"><?= $esc(strtoupper(substr($profile['name'], 0, 1))) ?></div><?php endif; ?><h1><?= $esc($profile['name']) ?></h1><p><?= $esc(ucfirst($profile['role'])) ?> · <?= $esc($profile['username']) ?></p></aside><section class="profile-card"><h2>Update your profile</h2><?php if ($message !== ''): ?><p class="notice"><?= $esc($message) ?></p><?php endif; ?><?php if ($error !== ''): ?><p class="notice error"><?= $esc($error) ?></p><?php endif; ?><form class="profile-form" method="post" enctype="multipart/form-data"><label>Full name<input name="name" value="<?= $esc($profile['name']) ?>" required></label><label>Email<input name="email" type="email" value="<?= $esc($profile['email']) ?>" placeholder="you@example.com"></label><label>Phone<input name="phone" value="<?= $esc($profile['phone']) ?>" placeholder="+255 ..."></label><label>Profile photo<input name="photo" type="file" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG, or WEBP under 5 MB.</small></label><label>Short profile<textarea name="bio" placeholder="Your role, expertise, interests, or area of work"><?= $esc($profile['bio']) ?></textarea></label><button class="button button-red" type="submit">Save profile <span>→</span></button></form></section></div></main><footer class="profile-footer"><p>BRD Research Consulting Centre · Profile</p><a href="logout.php">Sign out ↗</a></footer></div></body></html>
