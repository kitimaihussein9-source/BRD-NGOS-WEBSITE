<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
  header('Location: index.php');
  exit;
}
require_once __DIR__ . DIRECTORY_SEPARATOR . 'db.php';
$database = db();
$reportSummary = [
    ['value' => $database->query('SELECT COUNT(*) FROM requests')->fetchColumn(), 'label' => 'Total requests'],
    ['value' => $database->query("SELECT COUNT(*) FROM requests WHERE status IN ('Submitted', 'Under review')")->fetchColumn(), 'label' => 'Active follow-ups'],
    ['value' => $database->query('SELECT COUNT(*) FROM publications')->fetchColumn(), 'label' => 'Publications'],
    ['value' => $database->query('SELECT COUNT(*) FROM events')->fetchColumn(), 'label' => 'Events']
];
$filterStatus = trim((string) ($_GET['status'] ?? ''));
$filterType = trim((string) ($_GET['type'] ?? ''));
$where = [];
$parameters = [];
if ($filterStatus !== '') {
  $where[] = 'status = ?';
  $parameters[] = $filterStatus;
}
if ($filterType !== '') {
  $where[] = 'type = ?';
  $parameters[] = $filterType;
}
$requestQuery = 'SELECT name, type, subject, status, payment_status, created_at FROM requests' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY created_at DESC';
$requestStatement = $database->prepare($requestQuery);
$requestStatement->execute($parameters);
$recentRequests = $requestStatement->fetchAll();
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="brd-request-report.csv"');
  $output = fopen('php://output', 'w');
  fputcsv($output, ['Applicant', 'Type', 'Subject', 'Status', 'Payment status', 'Submitted']);
  foreach ($recentRequests as $request) {
    fputcsv($output, [$request['name'], $request['type'], $request['subject'], $request['status'], $request['payment_status'], $request['created_at']]);
  }
  fclose($output);
  exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BRD Reports</title>
  <link rel="stylesheet" href="styles.css" />
  <style>
    .page-shell { min-height: 100vh; background: var(--cream); }
    .page-header, .page-main, .page-footer { max-width: 1240px; margin: auto; }
    .page-header { display: flex; justify-content: space-between; align-items: center; padding: 26px 32px; border-bottom: 1px solid #d9dcd7; }
    .page-main { padding: 56px 32px 80px; }
    .stats-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
    .stat-card { background: #fff; padding: 34px 24px; border-top: 4px solid var(--red); }
    .stat-card:nth-child(2) { border-color: var(--green); }
    .stat-card:nth-child(3) { border-color: var(--navy); }
    .stat-card:nth-child(4) { border-color: #d09a20; }
    .stat-card strong { display: block; color: var(--navy); font: 600 48px/1 "Barlow Condensed", sans-serif; }
    .stat-card span { display: block; color: var(--muted); margin-top: 8px; font-size: 13px; }
    .report-grid { display: grid; grid-template-columns: 1.2fr .8fr; gap: 22px; margin-top: 24px; }
    .panel { background: #fff; padding: 28px; }
    .panel h2 { margin: 0 0 18px; color: var(--navy); font: 600 36px/1 "Barlow Condensed", sans-serif; text-transform: uppercase; }
    .request-list { border-top: 1px solid #d9dcd7; }
    .request-row { display: grid; grid-template-columns: 1.2fr .8fr .8fr; gap: 16px; border-bottom: 1px solid #d9dcd7; padding: 13px 0; color: var(--muted); font-size: 12px; }
    .request-row strong { color: var(--navy); }
    .report-tools { display: flex; gap: 10px; align-items: end; flex-wrap: wrap; margin: 20px 0; }
    .report-tools label { color: var(--muted); font-size: 11px; font-weight: 700; }
    .report-tools select, .report-tools button { display: block; margin-top: 5px; padding: 9px; border: 1px solid #c9ceca; background: #fff; font: 12px "DM Sans", sans-serif; }
    .report-tools button { background: var(--navy); color: #fff; cursor: pointer; }
    .report-export { margin-left: auto; color: var(--navy); font-size: 12px; font-weight: 700; text-decoration: none; }
    .insight-box { background: var(--navy); color: #fff; padding: 28px; }
    .insight-box h3 { margin: 0 0 12px; font: 600 32px/1 "Barlow Condensed", sans-serif; text-transform: uppercase; }
    .insight-box p { color: #dfe4f4; line-height: 1.7; }
    .page-footer { padding: 0 32px 28px; display: flex; justify-content: space-between; color: var(--muted); font-size: 12px; }
    .page-footer a { color: var(--navy); text-decoration: none; font-weight: 700; }
    @media (max-width: 760px) { .page-header, .page-main, .page-footer { padding-left: 20px; padding-right: 20px; } .stats-grid, .report-grid { grid-template-columns: 1fr; } .page-footer { display: block; } }
  </style>
</head>
<body>
  <div class="topline"></div>
  <div class="page-shell">
    <header class="page-header">
      <a class="brand" href="index.php"><span class="brand-mark"><b>B</b><b class="torch">R<span>●</span></b><b>D<i>••</i></b></span><span class="brand-subtitle">Building Resilience to Disasters</span></a>
      <a class="text-link" href="index.php">Back to home <span>↗</span></a>
    </header>
    <main class="page-main">
      <section class="page-panel" style="background:#fff;padding:30px;border-top:4px solid var(--red);margin-bottom:24px;">
        <p class="eyebrow">BRD reporting</p>
        <h1 style="margin:0;color:var(--navy);font:600 clamp(42px,5vw,72px)/.9 'Barlow Condensed',sans-serif;text-transform:uppercase;">Operational overview</h1>
      </section>
      <div class="stats-grid">
        <?php foreach ($reportSummary as $stat): ?>
          <div class="stat-card">
            <strong><?= htmlspecialchars((string) $stat['value'], ENT_QUOTES, 'UTF-8') ?></strong>
            <span><?= htmlspecialchars((string) $stat['label'], ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="report-grid">
        <section class="panel">
          <h2>Recent requests</h2>
          <form class="report-tools" method="get">
            <label>Status<select name="status"><option value="">All statuses</option><option value="Submitted" <?= $filterStatus === 'Submitted' ? 'selected' : '' ?>>Submitted</option><option value="Under review" <?= $filterStatus === 'Under review' ? 'selected' : '' ?>>Under review</option><option value="Confirmed" <?= $filterStatus === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option></select></label>
            <label>Type<select name="type"><option value="">All request types</option><option value="admission" <?= $filterType === 'admission' ? 'selected' : '' ?>>Admission</option><option value="training" <?= $filterType === 'training' ? 'selected' : '' ?>>Training</option><option value="consultancy" <?= $filterType === 'consultancy' ? 'selected' : '' ?>>Consultancy</option><option value="research-support" <?= $filterType === 'research-support' ? 'selected' : '' ?>>Research support</option></select></label>
            <button type="submit">Filter</button>
            <a class="report-export" href="reports.php?status=<?= urlencode($filterStatus) ?>&amp;type=<?= urlencode($filterType) ?>&amp;export=csv">Download CSV ↗</a>
          </form>
          <div class="request-list">
            <?php foreach ($recentRequests as $request): ?>
              <div class="request-row">
                <strong><?= htmlspecialchars($request['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                <span><?= htmlspecialchars(ucfirst((string) $request['type']), ENT_QUOTES, 'UTF-8') ?><br><?= htmlspecialchars($request['subject'], ENT_QUOTES, 'UTF-8') ?></span>
                <span><?= htmlspecialchars((string) $request['status'], ENT_QUOTES, 'UTF-8') ?><br><?= htmlspecialchars((string) $request['payment_status'], ENT_QUOTES, 'UTF-8') ?></span>
              </div>
            <?php endforeach; ?>
            <?php if (!$recentRequests): ?><p>No requests match the selected filters.</p><?php endif; ?>
          </div>
        </section>
        <aside class="panel insight-box">
          <h3>Impact focus</h3>
          <p>BRD is centred on practical resilience work: building local capacity, improving disaster data, supporting preparedness, and helping communities respond with clearer evidence and stronger planning.</p>
          <p>Each request, publication, project, and event supports a wider resilience and climate adaptation agenda.</p>
        </aside>
      </div>
    </main>
    <footer class="page-footer">
      <p>BRD Research Consulting Centre · operational reports</p>
      <a href="dashboard.php">Governance dashboard ↗</a>
    </footer>
  </div>
</body>
</html>
