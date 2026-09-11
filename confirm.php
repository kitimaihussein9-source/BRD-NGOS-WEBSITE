<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$user = $_SESSION['user'];
$displayName = htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');
$displayRole = htmlspecialchars(ucfirst($user['role']), ENT_QUOTES, 'UTF-8');
$workspace = $user['role'] === 'client' ? 'client.php' : 'dashboard.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal confirmation | BRD Research Consulting Centre</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    .confirmation-page{min-height:100vh;display:grid;place-items:center;padding:32px;background:var(--cream)}
    .confirmation-card{width:min(100%,620px);padding:clamp(32px,7vw,72px);background:#fff;border-top:8px solid var(--green);box-shadow:0 20px 60px #17202718}
    .confirmation-mark{width:58px;height:58px;display:grid;place-items:center;margin-bottom:28px;border-radius:50%;background:var(--green);color:#fff;font-size:32px}
    .confirmation-card h1{margin:12px 0 16px;color:var(--navy);font:600 clamp(48px,8vw,78px)/.9 "Barlow Condensed",sans-serif;text-transform:uppercase}
    .confirmation-card p{max-width:460px;color:var(--muted);line-height:1.7}
    .confirmation-card strong{color:var(--ink)}
    .confirmation-actions{display:flex;gap:24px;align-items:center;margin-top:32px;flex-wrap:wrap}
  </style>
</head>
<body>
  <div class="topline"></div>
  <main class="confirmation-page">
    <section class="confirmation-card" aria-labelledby="confirmation-title">
      <div class="confirmation-mark" aria-hidden="true">✓</div>
      <p class="eyebrow">BRD research portal</p>
      <h1 id="confirmation-title">You are in.</h1>
      <p>Welcome, <strong><?= $displayName ?></strong>. Your <strong><?= $displayRole ?></strong> session has been confirmed and your workspace is ready.</p>
      <div class="confirmation-actions">
        <a class="button button-red" href="<?= $workspace ?>">Open workspace <span>→</span></a>
        <a class="text-link" href="index.php">Return to BRD <span>↗</span></a>
        <a class="text-link" href="logout.php">Sign out <span>↗</span></a>
      </div>
    </section>
  </main>
</body>
</html>
