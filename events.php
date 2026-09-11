<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'db.php';
$database = db();
$events = $database->query('SELECT * FROM events ORDER BY event_date ASC')->fetchAll();
if (!$events) {
    $events = [
        ['title' => 'Community resilience planning clinic', 'event_date' => '2026-10-12', 'location' => 'Dar es Salaam', 'details' => 'A practical session on local planning, preparedness and resilience coordination.'],
        ['title' => 'Climate adaptation and GIS workshop', 'event_date' => '2026-11-05', 'location' => 'Morogoro', 'details' => 'A focused workshop on spatial risk mapping and adaptation planning.'],
        ['title' => 'Disaster preparedness outreach week', 'event_date' => '2026-12-01', 'location' => 'Kigoma', 'details' => 'Community engagement and risk communication with local leaders and youth groups.']
    ];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BRD Events</title>
  <link rel="stylesheet" href="styles.css" />
  <style>
    .page-shell { min-height: 100vh; background: var(--cream); }
    .page-header, .page-main, .page-footer { max-width: 1240px; margin: auto; }
    .page-header { display: flex; justify-content: space-between; align-items: center; padding: 26px 32px; border-bottom: 1px solid #d9dcd7; }
    .page-main { padding: 56px 32px 80px; }
    .event-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
    .event-card { background: #fff; padding: 24px; border-top: 4px solid var(--green); }
    .event-card h2 { margin: 0 0 12px; color: var(--navy); font: 600 28px/1 "Barlow Condensed", sans-serif; text-transform: uppercase; }
    .event-card p { color: var(--muted); line-height: 1.7; }
    .event-date { display: inline-block; margin-bottom: 10px; padding: 7px 10px; background: #e9e4d5; color: var(--red); font-size: 10px; font-weight: 700; text-transform: uppercase; }
    .event-action { display: inline-block; margin-top: 8px; color: var(--navy); font-size: 12px; font-weight: 700; text-decoration: none; }
    .page-footer { padding: 0 32px 28px; display: flex; justify-content: space-between; color: var(--muted); font-size: 12px; }
    .page-footer a { color: var(--navy); text-decoration: none; font-weight: 700; }
    @media (max-width: 760px) { .page-header, .page-main, .page-footer { padding-left: 20px; padding-right: 20px; } .event-grid { grid-template-columns: 1fr; } .page-footer { display: block; } }
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
      <section class="page-panel" style="background:#fff;padding:30px;border-top:4px solid var(--navy);margin-bottom:24px;">
        <p class="eyebrow">BRD calendar</p>
        <h1 style="margin:0;color:var(--navy);font:600 clamp(42px,5vw,72px)/.9 'Barlow Condensed',sans-serif;text-transform:uppercase;">Events and community engagement</h1>
      </section>
      <div class="event-grid">
        <?php foreach ($events as $event): ?>
          <article class="event-card">
            <span class="event-date"><?= htmlspecialchars(date('d M Y', strtotime((string) ($event['event_date'] ?? 'now'))), ENT_QUOTES, 'UTF-8') ?></span>
            <h2><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p><strong>Location:</strong> <?= htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><?= htmlspecialchars($event['details'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php if (!empty($event['id'])): ?><a class="event-action" href="event_ics.php?id=<?= (int) $event['id'] ?>">Add to calendar <span>↗</span></a><?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    </main>
    <footer class="page-footer">
      <p>BRD Research Consulting Centre · Event calendar</p>
      <a href="admissions.php">Explore BRD programmes ↗</a>
    </footer>
  </div>
</body>
</html>
