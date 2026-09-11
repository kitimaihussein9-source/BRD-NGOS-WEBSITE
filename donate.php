<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'db.php';
$database = db();
$donations = $database->query('SELECT * FROM donations ORDER BY created_at DESC LIMIT 6')->fetchAll();
if (!$donations) {
    $donations = [
        ['supporter_name' => 'Tanzania Resilience Fund', 'support_type' => 'Partnership', 'amount' => 'TZS 5,000,000', 'note' => 'Supporting community preparedness outreach and field tools.'],
        ['supporter_name' => 'Green Horizon Collective', 'support_type' => 'Donation', 'amount' => 'TZS 2,350,000', 'note' => 'Support for coastal risk mapping and outreach.'],
        ['supporter_name' => 'Kigoma Youth Network', 'support_type' => 'In-kind support', 'amount' => 'TZS 1,100,000', 'note' => 'Volunteer mobilization and community training materials.']
    ];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Support BRD</title>
  <link rel="stylesheet" href="styles.css" />
  <style>
    .page-shell { min-height: 100vh; background: var(--cream); }
    .page-header, .page-main, .page-footer { max-width: 1240px; margin: auto; }
    .page-header { display: flex; justify-content: space-between; align-items: center; padding: 26px 32px; border-bottom: 1px solid #d9dcd7; }
    .page-main { padding: 56px 32px 80px; }
    .donate-grid { display: grid; grid-template-columns: 1.1fr .9fr; gap: 22px; }
    .page-panel { background: #fff; padding: 30px; border-top: 4px solid var(--red); }
    .page-panel h1, .page-panel h2 { margin: 0 0 16px; color: var(--navy); font: 600 clamp(42px, 5vw, 72px)/.9 "Barlow Condensed", sans-serif; text-transform: uppercase; }
    .page-panel p { color: var(--muted); line-height: 1.7; }
    .donate-card { background: var(--navy); color: #fff; padding: 28px; }
    .donate-card h3 { margin: 0 0 14px; font: 600 28px/1 "Barlow Condensed", sans-serif; text-transform: uppercase; }
    .donate-card p, .donate-card a { color: #e8ebf7; }
    .support-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; margin-top: 26px; }
    .support-card { background: #fff; padding: 24px; border-left: 4px solid var(--green); }
    .support-card h3 { margin: 0 0 12px; color: var(--navy); font: 600 30px/1 "Barlow Condensed", sans-serif; text-transform: uppercase; }
    .support-card p { color: var(--muted); line-height: 1.7; }
    .page-footer { padding: 0 32px 28px; display: flex; justify-content: space-between; color: var(--muted); font-size: 12px; }
    .page-footer a { color: var(--navy); text-decoration: none; font-weight: 700; }
    @media (max-width: 760px) { .page-header, .page-main, .page-footer { padding-left: 20px; padding-right: 20px; } .donate-grid, .support-grid { grid-template-columns: 1fr; } .page-footer { display: block; } }
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
      <div class="donate-grid">
        <section class="page-panel">
          <p class="eyebrow">Support BRD</p>
          <h1>Partner with us to build resilient communities.</h1>
          <p>BRD works with communities, institutions and partners to strengthen disaster preparedness, risk reduction, research capacity and climate resilience. Your support enables local action and strong evidence-based interventions.</p>
          <p>Donations and partnerships help fund projects, training, outreach and community response work.</p>
        </section>
        <aside class="page-panel donate-card">
          <h3>How to support</h3>
          <p>Financial donations, in-kind support, and institutional partnerships are all welcome.</p>
          <p>Email: <a href="mailto:dfngussa@gmail.com">dfngussa@gmail.com</a></p>
          <p>Phone: <a href="tel:+255758821316">+255 758 821 316</a></p>
        </aside>
      </div>
      <div class="support-grid">
        <?php foreach ($donations as $donation): ?>
          <article class="support-card">
            <h3><?= htmlspecialchars($donation['supporter_name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><strong><?= htmlspecialchars($donation['support_type'], ENT_QUOTES, 'UTF-8') ?></strong></p>
            <p><?= htmlspecialchars($donation['amount'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><?= htmlspecialchars($donation['note'], ENT_QUOTES, 'UTF-8') ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </main>
    <footer class="page-footer">
      <p>BRD Research Consulting Centre · Support and partnership</p>
      <a href="admissions.php">Apply for BRD support ↗</a>
    </footer>
  </div>
</body>
</html>
