<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'db.php';
$database = db();
$publications = $database->query('SELECT * FROM publications ORDER BY created_at DESC')->fetchAll();
if (!$publications) {
    $publications = [
      ['title' => 'Climate and disaster resilience brief', 'type' => 'Policy brief', 'details' => 'A concise summary of resilience planning priorities and community adaptation strategies.'],
      ['title' => 'Research methodology guide for local teams', 'type' => 'Guide', 'details' => 'Practical guidance for effective field design, sampling, data quality, and analysis.'],
      ['title' => 'Community disaster preparedness report', 'type' => 'Report', 'details' => 'An evidence-based report on community response practice and resilience initiatives.'],
      ['title' => 'GIS and mapping for local planning', 'type' => 'Training resource', 'details' => 'A learning resource for spatial risk understanding and local planning decisions.']
    ];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BRD Publications</title>
  <link rel="stylesheet" href="styles.css" />
  <style>
    .page-shell { min-height: 100vh; background: var(--cream); }
    .page-header, .page-main, .page-footer { max-width: 1240px; margin: auto; }
    .page-header { display: flex; justify-content: space-between; align-items: center; padding: 26px 32px; border-bottom: 1px solid #d9dcd7; }
    .page-main { padding: 56px 32px 80px; }
    .publication-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
    .publication-card { background: #fff; padding: 24px; border-left: 4px solid var(--red); }
    .publication-card h2 { margin: 0 0 10px; color: var(--navy); font: 600 28px/1 "Barlow Condensed", sans-serif; text-transform: uppercase; }
    .publication-card p { margin: 0; color: var(--muted); font-size: 13px; }
    .page-footer { padding: 0 32px 28px; display: flex; justify-content: space-between; color: var(--muted); font-size: 12px; }
    .page-footer a { color: var(--navy); text-decoration: none; font-weight: 700; }
    @media (max-width: 760px) { .page-header, .page-main, .page-footer { padding-left: 20px; padding-right: 20px; } .publication-grid { grid-template-columns: 1fr; } .page-footer { display: block; } }
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
        <p class="eyebrow">Knowledge and learning</p>
        <h1 style="margin:0;color:var(--navy);font:600 clamp(42px,5vw,72px)/.9 'Barlow Condensed',sans-serif;text-transform:uppercase;">Publications and resources</h1>
      </section>
      <div class="publication-grid">
        <?php foreach ($publications as $publication): ?>
          <article class="publication-card">
            <h2><?= htmlspecialchars($publication['title'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars($publication['type'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php if (!empty($publication['details'])): ?><p style="margin-top:8px;"><?= htmlspecialchars($publication['details'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    </main>
    <footer class="page-footer">
      <p>BRD Research Consulting Centre · Publications</p>
      <a href="admissions.php">Apply for BRD support ↗</a>
    </footer>
  </div>
</body>
</html>
