<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'db.php';
$database = db();
$projects = $database->query('SELECT * FROM projects ORDER BY created_at DESC')->fetchAll();
if (!$projects) {
    $projects = [
      ['title' => 'Community resilience baseline', 'location' => 'Dar es Salaam', 'status' => 'Active', 'details' => 'Research and data collection project focused on household resilience and local preparedness.'],
      ['title' => 'Flood risk mapping', 'location' => 'Kigoma', 'status' => 'In review', 'details' => 'Spatial assessment for flood-prone communities and preparedness planning support.'],
      ['title' => 'Climate adaptation review', 'location' => 'Morogoro', 'status' => 'Planned', 'details' => 'Assessment of local adaptation needs and resilience measures in climate-sensitive areas.']
    ];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BRD Projects</title>
  <link rel="stylesheet" href="styles.css" />
  <style>
    .page-shell { min-height: 100vh; background: var(--cream); }
    .page-header, .page-main, .page-footer { max-width: 1240px; margin: auto; }
    .page-header { display: flex; justify-content: space-between; align-items: center; padding: 26px 32px; border-bottom: 1px solid #d9dcd7; }
    .page-main { padding: 56px 32px 80px; }
    .project-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
    .project-card { background: #fff; padding: 24px; border-top: 4px solid var(--navy); }
    .project-card h2 { margin: 0 0 12px; color: var(--navy); font: 600 30px/1 "Barlow Condensed", sans-serif; text-transform: uppercase; }
    .project-card p { color: var(--muted); line-height: 1.7; }
    .project-tag { display: inline-block; margin-top: 10px; padding: 7px 10px; background: #e9e4d5; color: var(--green); font-size: 10px; font-weight: 700; text-transform: uppercase; }
    .page-footer { padding: 0 32px 28px; display: flex; justify-content: space-between; color: var(--muted); font-size: 12px; }
    .page-footer a { color: var(--navy); text-decoration: none; font-weight: 700; }
    @media (max-width: 760px) { .page-header, .page-main, .page-footer { padding-left: 20px; padding-right: 20px; } .project-grid { grid-template-columns: 1fr; } .page-footer { display: block; } }
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
        <p class="eyebrow">BRD programmes</p>
        <h1 style="margin:0;color:var(--navy);font:600 clamp(42px,5vw,72px)/.9 'Barlow Condensed',sans-serif;text-transform:uppercase;">Projects and interventions</h1>
        <p style="color:var(--muted);line-height:1.7;max-width:760px;">BRD’s work is shaped by practical, community-focused interventions that connect research, risk understanding, and resilience-building action.</p>
      </section>
      <div class="project-grid">
        <?php foreach ($projects as $project): ?>
          <article class="project-card">
            <h2><?= htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p><strong>Location:</strong> <?= htmlspecialchars($project['location'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><?= htmlspecialchars($project['details'], ENT_QUOTES, 'UTF-8') ?></p>
            <span class="project-tag"><?= htmlspecialchars($project['status'], ENT_QUOTES, 'UTF-8') ?></span>
          </article>
        <?php endforeach; ?>
      </div>
    </main>
    <footer class="page-footer">
      <p>BRD Research Consulting Centre · Project portfolio</p>
      <a href="admissions.php">View admissions ↗</a>
    </footer>
  </div>
</body>
</html>
