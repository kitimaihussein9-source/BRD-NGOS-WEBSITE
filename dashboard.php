<?php
session_start();
require_once __DIR__ . DIRECTORY_SEPARATOR . 'db.php';
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$user = $_SESSION['user'];
$role = $user['role'];
if ($role === 'client') {
  header('Location: client.php');
  exit;
}
$name = htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');
$roleLabel = htmlspecialchars(ucfirst($role), ENT_QUOTES, 'UTF-8');

$dashboardData = [
    'admin' => [
        'eyebrow' => 'Administration desk',
        'title' => 'Keep the work moving.',
        'intro' => 'A clear view of people, projects, and research activity across BRD.',
        'focus' => 'Three requests need your attention before the end of today.',
        'focusDetail' => 'Review the Kigamboni household survey and approve two pending submissions.',
        'stats' => [['value' => '24', 'label' => 'Active projects'], ['value' => '08', 'label' => 'New requests'], ['value' => '12', 'label' => 'Team members']],
        'section' => 'Recent activity',
        'items' => [['title' => 'Kigamboni household survey', 'meta' => 'Client request · 2 hours ago', 'status' => 'Review'], ['title' => 'GIS training cohort 04', 'meta' => 'Assistant update · Yesterday', 'status' => 'On track'], ['title' => 'M&E proposal development', 'meta' => 'New enquiry · 2 days ago', 'status' => 'New']],
        'actions' => ['Manage users', 'Review requests', 'View reports'],
    ],
    'client' => [
        'eyebrow' => 'Client workspace',
        'title' => 'Turn your question into progress.',
        'intro' => 'Track your requests, share project context, and keep every research milestone visible.',
        'focus' => 'Your community resilience baseline is moving steadily.',
        'focusDetail' => 'Data collection is 68% complete. The next update is expected tomorrow.',
        'stats' => [['value' => '03', 'label' => 'Active projects'], ['value' => '02', 'label' => 'Awaiting review'], ['value' => '06', 'label' => 'Shared files']],
        'section' => 'Your projects',
        'items' => [['title' => 'Community resilience baseline', 'meta' => 'Data collection · 68% complete', 'status' => 'Active'], ['title' => 'Market access study', 'meta' => 'Research design · Updated today', 'status' => 'Review'], ['title' => 'Training evaluation', 'meta' => 'Results shared · 14 August', 'status' => 'Complete']],
        'actions' => ['Start a request', 'Upload a file', 'Message BRD'],
    ],
    'assistant' => [
        'eyebrow' => 'Assistant workspace',
        'title' => 'Make the next step count.',
        'intro' => 'See your assignments, deadlines, and the practical work supporting each research project.',
        'focus' => 'Two deliverables are due this week.',
        'focusDetail' => 'Start with the household survey data cleaning task, due today.',
        'stats' => [['value' => '05', 'label' => 'Assigned projects'], ['value' => '11', 'label' => 'Open tasks'], ['value' => '02', 'label' => 'Due this week']],
        'section' => 'Today\'s priorities',
        'items' => [['title' => 'Clean household survey data', 'meta' => 'Kigamboni baseline · Due today', 'status' => 'Priority'], ['title' => 'Prepare ODK deployment', 'meta' => 'Community resilience · Due tomorrow', 'status' => 'In progress'], ['title' => 'Update literature matrix', 'meta' => 'Market access study · Friday', 'status' => 'Queued']],
        'actions' => ['Open assignments', 'Upload deliverable', 'Ask for support'],
    ],
];
$data = $dashboardData[$role];
$actionLink = in_array($role, ['admin', 'assistant'], true) ? 'manage.php' : 'index.php#contact';
$database = db();
if ($role === 'admin') {
  $data['stats'][0]['value'] = str_pad((string) $database->query('SELECT COUNT(*) FROM requests')->fetchColumn(), 2, '0', STR_PAD_LEFT);
  $data['stats'][1]['value'] = str_pad((string) $database->query("SELECT COUNT(*) FROM requests WHERE status IN ('Submitted', 'Under review')")->fetchColumn(), 2, '0', STR_PAD_LEFT);
  $data['stats'][2]['value'] = str_pad((string) $database->query('SELECT COUNT(*) FROM users')->fetchColumn(), 2, '0', STR_PAD_LEFT);
} elseif ($role === 'assistant') {
  $data['stats'][1]['value'] = str_pad((string) $database->query("SELECT COUNT(*) FROM content WHERE type = 'course'")->fetchColumn(), 2, '0', STR_PAD_LEFT);
  $data['stats'][2]['value'] = str_pad((string) $database->query("SELECT COUNT(*) FROM requests WHERE status IN ('Submitted', 'Under review')")->fetchColumn(), 2, '0', STR_PAD_LEFT);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $roleLabel ?> dashboard | BRD Research Consulting Centre</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    .dashboard-shell{min-height:100vh;background:var(--cream)}
    .dashboard-header{max-width:1240px;margin:auto;padding:28px 32px 26px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #d9dcd7}
    .dashboard-header .brand-mark{transform:scale(.84);transform-origin:left top;margin-bottom:-5px}
    .dashboard-header .brand-subtitle{font-size:8px}
    .dashboard-user{display:flex;align-items:center;gap:20px}.user-chip{display:flex;align-items:center;gap:10px;color:var(--muted);font-size:13px}.user-initial{display:grid;place-items:center;width:38px;height:38px;background:var(--navy);color:#fff;font-weight:700;border-radius:50%}
    .dashboard-nav{max-width:1240px;margin:auto;padding:0 32px;border-bottom:1px solid #d9dcd7;display:flex;gap:26px}.dashboard-nav a{padding:13px 0;color:var(--muted);font-size:12px;font-weight:700;text-decoration:none;text-transform:uppercase;letter-spacing:1px}.dashboard-nav a:first-child{color:var(--red);border-bottom:2px solid var(--red)}
    .dashboard-main{max-width:1240px;margin:auto;padding:58px 32px 84px}.dashboard-hero{display:flex;justify-content:space-between;gap:36px;align-items:end;margin-bottom:40px}.dashboard-hero h1{max-width:620px;margin:12px 0 15px;color:var(--navy);font:600 clamp(52px,7vw,86px)/.88 "Barlow Condensed",sans-serif;text-transform:uppercase}.dashboard-hero p{max-width:520px;color:var(--muted);line-height:1.7}.dashboard-date{color:var(--red);font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1.4px;white-space:nowrap}
    .dashboard-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:56px}.stat-card{padding:26px;background:#fff;border-top:4px solid var(--red)}.stat-card:nth-child(2){border-color:var(--green)}.stat-card:nth-child(3){border-color:var(--navy)}.stat-value{display:block;color:var(--navy);font:600 50px/1 "Barlow Condensed",sans-serif}.stat-label{display:block;margin-top:8px;color:var(--muted);font-size:13px}
    .focus-band{display:flex;justify-content:space-between;gap:28px;align-items:center;margin:-28px 0 54px;padding:22px 26px;background:#e9e4d5;border-left:5px solid var(--red)}.focus-band strong{display:block;color:var(--navy);font-size:16px}.focus-band p{margin:6px 0 0;color:var(--muted);font-size:13px}.focus-link{color:var(--navy);font-size:12px;font-weight:700;text-decoration:none;white-space:nowrap}
    .dashboard-content{display:grid;grid-template-columns:1fr 280px;gap:52px}.dashboard-section-title{display:flex;justify-content:space-between;align-items:baseline;border-bottom:2px solid var(--navy);padding-bottom:14px;margin-bottom:0}.dashboard-section-title h2{margin:0;color:var(--navy);font:600 36px/1 "Barlow Condensed",sans-serif;text-transform:uppercase}.activity-item{display:flex;justify-content:space-between;gap:18px;align-items:center;padding:23px 0;border-bottom:1px solid #d9dcd7}.activity-item h3{margin:0 0 7px;font-size:16px}.activity-item p{margin:0;color:var(--muted);font-size:13px}.status{padding:7px 10px;color:#fff;background:var(--navy);font-size:11px;font-weight:700;text-transform:uppercase;white-space:nowrap}.status:nth-child(2){background:var(--green)}.dashboard-actions{padding:25px;background:var(--navy);color:#fff}.dashboard-actions h2{margin:0 0 20px;font:600 32px/1 "Barlow Condensed",sans-serif;text-transform:uppercase}.dashboard-action{display:block;padding:14px 0;border-top:1px solid #ffffff4d;color:#fff;text-decoration:none;font-size:13px}.dashboard-action span{float:right;color:#e7bc42}.dashboard-footer{max-width:1240px;margin:auto;padding:0 32px 28px;color:var(--muted);font-size:12px;display:flex;justify-content:space-between}.dashboard-footer a{color:var(--navy);font-weight:700;text-decoration:none}
    @media(max-width:760px){.dashboard-header{padding:22px 20px}.dashboard-user{gap:10px}.user-chip span{display:none}.dashboard-nav{padding:0 20px;gap:18px;overflow:auto}.dashboard-nav a{white-space:nowrap}.dashboard-main{padding:42px 20px 60px}.dashboard-hero{display:block}.dashboard-date{display:block;margin-top:22px}.dashboard-stats{grid-template-columns:1fr;margin-bottom:42px}.focus-band{display:block;margin:-14px 0 42px;padding:20px}.focus-link{display:inline-block;margin-top:16px}.dashboard-content{display:block}.dashboard-actions{margin-top:42px}.dashboard-footer{padding:0 20px 22px;display:block}.dashboard-footer p{margin-bottom:10px}.activity-item{align-items:start;flex-direction:column;gap:12px}}
  </style>
</head>
<body>
  <div class="topline"></div>
  <div class="dashboard-shell">
    <header class="dashboard-header">
      <a class="brand" href="index.php" aria-label="BRD home"><span class="brand-mark"><b>B</b><b class="torch">R<span>●</span></b><b>D<i>••</i></b></span><span class="brand-subtitle">Building Resilience to Disasters</span></a>
      <div class="dashboard-user"><div class="user-chip"><span><?= $name ?></span><span class="user-initial"><?= strtoupper(substr($user['name'], 0, 1)) ?></span></div><?php if (in_array($role, ['admin', 'assistant'], true)): ?><a class="text-link" href="manage.php">Operations <span>→</span></a><?php endif; ?><a class="text-link" href="logout.php">Sign out <span>↗</span></a></div>
    </header>
    <nav class="dashboard-nav" aria-label="Dashboard navigation"><a href="#overview">Overview</a><a href="#workspace">Workspace</a><a href="#actions">Quick actions</a><a href="index.php#contact">Support</a></nav>
    <main class="dashboard-main">
      <section class="dashboard-hero" id="overview"><div><p class="eyebrow"><?= htmlspecialchars($data['eyebrow'], ENT_QUOTES, 'UTF-8') ?></p><h1><?= htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8') ?></h1><p><?= htmlspecialchars($data['intro'], ENT_QUOTES, 'UTF-8') ?></p></div><span class="dashboard-date"><?= date('F j, Y') ?></span></section>
      <section class="dashboard-stats" aria-label="Workspace summary">
        <?php foreach ($data['stats'] as $stat): ?><div class="stat-card"><span class="stat-value"><?= htmlspecialchars($stat['value'], ENT_QUOTES, 'UTF-8') ?></span><span class="stat-label"><?= htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8') ?></span></div><?php endforeach; ?>
      </section>
      <section class="focus-band"><div><strong><?= htmlspecialchars($data['focus'], ENT_QUOTES, 'UTF-8') ?></strong><p><?= htmlspecialchars($data['focusDetail'], ENT_QUOTES, 'UTF-8') ?></p></div><a class="focus-link" href="#workspace">View workspace <span>→</span></a></section>
      <div class="dashboard-content"><section id="workspace"><div class="dashboard-section-title"><h2><?= htmlspecialchars($data['section'], ENT_QUOTES, 'UTF-8') ?></h2><span class="eyebrow">Live view</span></div><?php foreach ($data['items'] as $item): ?><article class="activity-item"><div><h3><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars($item['meta'], ENT_QUOTES, 'UTF-8') ?></p></div><span class="status"><?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?></span></article><?php endforeach; ?></section><aside class="dashboard-actions" id="actions"><h2>Quick actions</h2><?php foreach ($data['actions'] as $action): ?><a class="dashboard-action" href="<?= $actionLink ?>"><?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?><span>→</span></a><?php endforeach; ?></aside></div>
    </main>
    <footer class="dashboard-footer"><p>BRD Research Consulting Centre · <?= $roleLabel ?> workspace</p><a href="confirm.php">Session confirmation ↑</a></footer>
  </div>
</body>
</html>
