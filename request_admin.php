<?php
session_start();
require_once __DIR__ . DIRECTORY_SEPARATOR . 'db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') { header('Location: index.php'); exit; }
$database = db();
$message = isset($_GET['done']) ? 'Request action completed.' : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim((string) ($_POST['request_id'] ?? ''));
    $action = $_POST['action'] ?? '';
    if ($id !== '' && $action === 'confirm') {
        $statement = $database->prepare("UPDATE requests SET status = 'Confirmed' WHERE id = ?");
        $statement->execute([$id]);
    } elseif ($id !== '' && $action === 'delete') {
        $statement = $database->prepare('DELETE FROM requests WHERE id = ?');
        $statement->execute([$id]);
    }
    header('Location: request_admin.php?done=1'); exit;
}
$requests = $database->query('SELECT * FROM requests ORDER BY created_at DESC')->fetchAll();
$esc = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?><!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Request desk | BRD</title><link rel="stylesheet" href="styles.css"><style>.desk-shell{min-height:100vh;background:var(--cream)}.desk-header,.desk-main,.desk-footer{max-width:1240px;margin:auto}.desk-header{padding:26px 32px;border-bottom:1px solid #d9dcd7;display:flex;justify-content:space-between}.desk-main{padding:56px 32px 80px}.desk-panel{background:#fff;padding:30px;border-top:4px solid var(--red)}.desk-panel h1{margin:10px 0 20px;color:var(--navy);font:600 72px/.9 'Barlow Condensed',sans-serif;text-transform:uppercase}.desk-table{border-top:1px solid #d9dcd7}.desk-row{display:grid;grid-template-columns:1.1fr 1fr 1fr 1.3fr;gap:14px;padding:16px 0;border-bottom:1px solid #d9dcd7;font-size:12px;align-items:center}.desk-row strong{color:var(--navy)}.desk-row a{color:var(--navy);font-weight:700}.desk-row form{display:inline}.desk-row button{border:1px solid #c9ceca;background:#fff;padding:7px 9px;cursor:pointer;font-size:11px}.desk-row .danger{color:var(--red)}.desk-note{padding:14px;background:#e6f0e8;color:var(--green);margin-bottom:20px}.desk-footer{padding:0 32px 28px;color:var(--muted);font-size:12px}@media(max-width:760px){.desk-header,.desk-main,.desk-footer{padding-left:20px;padding-right:20px}.desk-panel h1{font-size:56px}.desk-row{grid-template-columns:1fr;gap:7px}.desk-row form{margin-right:6px}}
</style></head><body><div class="topline"></div><div class="desk-shell"><header class="desk-header"><a class="brand" href="manage.php"><span class="brand-mark"><b>B</b><b class="torch">R<span>●</span></b><b>D<i>••</i></b></span><span class="brand-subtitle">Building Resilience to Disasters</span></a><a class="text-link" href="manage.php">Operations <span>↗</span></a></header><main class="desk-main"><section class="desk-panel"><p class="eyebrow">Admin request desk</p><h1>Retrieve, confirm, manage.</h1><?php if ($message !== ''): ?><p class="desk-note"><?= $esc($message) ?></p><?php endif; ?><div class="desk-table"><?php foreach ($requests as $request): ?><article class="desk-row"><strong><?= $esc($request['name']) ?><br><?= $esc($request['subject'] ?: ucfirst($request['type'])) ?></strong><span><?= $esc($request['email'] ?? 'No email') ?><br><?= $esc($request['phone'] ?? 'No phone') ?></span><span><?= $esc($request['course_name'] ?? '') ?><br><?= $esc($request['duration'] ?? '') ?><br><?= $esc($request['status']) ?></span><span><a href="request.php?id=<?= urlencode($request['id']) ?>">Retrieve full record ↗</a><form method="post"><input type="hidden" name="request_id" value="<?= $esc($request['id']) ?>"><input type="hidden" name="action" value="confirm"><button type="submit">Confirm</button></form><form method="post" onsubmit="return confirm('Delete this record?');"><input type="hidden" name="request_id" value="<?= $esc($request['id']) ?>"><input type="hidden" name="action" value="delete"><button class="danger" type="submit">Delete</button></form></span></article><?php endforeach; ?><?php if (!$requests): ?><p>No records have been submitted.</p><?php endif; ?></div></section></main><footer class="desk-footer">BRD Research Consulting Centre · secured admin request desk</footer></div></body></html>
