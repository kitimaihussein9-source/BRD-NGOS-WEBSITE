<?php
session_start();
require_once __DIR__ . DIRECTORY_SEPARATOR . 'db.php';
$database = db();
$esc = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ($_SESSION['user']['name'] ?? '')));
    $contact = trim((string) ($_POST['contact'] ?? ($_SESSION['user']['username'] ?? '')));
    $researchArea = trim((string) ($_POST['research_area'] ?? ''));
    $supportStage = trim((string) ($_POST['support_stage'] ?? ''));
    $topic = trim((string) ($_POST['topic'] ?? ''));
    $context = trim((string) ($_POST['context'] ?? ''));

    if ($name === '' || $contact === '' || $researchArea === '' || $supportStage === '' || $topic === '' || $context === '') {
        $message = 'Please complete every research support field before submitting.';
        $messageType = 'error';
    } else {
        $statement = $database->prepare('INSERT INTO requests (id, name, username, type, subject, message, status, payment_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $statement->execute([
            uniqid('brd_research_', true),
            $name,
            $contact,
            'research-support',
            $supportStage . ': ' . $topic,
            "Contact: {$contact}\nResearch area: {$researchArea}\nSupport stage: {$supportStage}\nResearch topic: {$topic}\nContext and expected support: {$context}",
            'Submitted',
            'Not applicable',
            date('Y-m-d H:i:s')
        ]);
        $message = 'Your research support request has been submitted. The BRD team will review the brief and contact you with the next step.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Request BRD support with research titles, objectives, problem statements, methods, data, and resilience studies." />
  <title>Research Support | BRD Research Consulting Centre</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=DM+Sans:wght@400;500;600;700&family=Libre+Baskerville:wght@400;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="styles.css" />
  <style>
    .research-shell { min-height: 100vh; background: var(--cream); }
    .research-header, .research-main, .research-footer { max-width: 1240px; margin: auto; }
    .research-header { display: flex; justify-content: space-between; align-items: center; padding: 26px 32px; border-bottom: 1px solid #d9dcd7; }
    .research-main { padding: 56px 32px 80px; }
    .research-hero { display: grid; grid-template-columns: 1.1fr .9fr; gap: 22px; align-items: end; margin-bottom: 24px; }
    .research-hero h1 { max-width: 720px; margin: 12px 0; color: var(--navy); font: 600 clamp(54px, 7vw, 92px)/.86 "Barlow Condensed", sans-serif; text-transform: uppercase; }
    .research-hero p:not(.eyebrow), .research-card p { color: var(--muted); line-height: 1.7; }
    .research-card { background: #fff; padding: 28px; border-top: 4px solid var(--red); }
    .research-card h2 { margin: 0 0 16px; color: var(--navy); font: 600 36px/1 "Barlow Condensed", sans-serif; text-transform: uppercase; }
    .research-services { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin: 22px 0; }
    .research-service { padding: 22px; background: var(--navy); color: #fff; }
    .research-service:nth-child(2) { background: var(--green); }
    .research-service:nth-child(3) { background: var(--red); }
    .research-service h3 { margin: 0 0 10px; font: 600 28px/1 "Barlow Condensed", sans-serif; text-transform: uppercase; }
    .research-service p { margin: 0; color: #fff; font-size: 13px; }
    .research-grid { display: grid; grid-template-columns: 1.1fr .9fr; gap: 22px; }
    .research-form label { display: block; margin: 14px 0; font-size: 12px; font-weight: 700; }
    .research-form input, .research-form select, .research-form textarea { display: block; width: 100%; margin-top: 7px; padding: 12px; border: 1px solid #c9ceca; background: #fff; color: var(--ink); font: 13px "DM Sans", sans-serif; }
    .research-form textarea { min-height: 120px; resize: vertical; }
    .research-form button { margin-top: 10px; border: 0; cursor: pointer; }
    .research-notice { margin: 0 0 22px; padding: 14px 18px; border-left: 4px solid var(--green); background: #e6f0e8; color: var(--green); font-size: 13px; }
    .research-notice.error { border-color: var(--red); background: #f5e5e3; color: var(--red); }
    .research-steps { margin: 0; padding-left: 20px; color: var(--muted); line-height: 2; }
    .research-footer { display: flex; justify-content: space-between; padding: 0 32px 28px; color: var(--muted); font-size: 12px; }
    .research-footer a { color: var(--navy); font-weight: 700; text-decoration: none; }
    @media (max-width: 760px) { .research-header, .research-main, .research-footer { padding-left: 20px; padding-right: 20px; } .research-hero, .research-grid, .research-services { grid-template-columns: 1fr; } .research-footer { display: block; } }
  </style>
</head>
<body>
  <div class="topline"></div>
  <div class="research-shell">
    <header class="research-header">
      <a class="brand" href="index.php" aria-label="BRD home"><span class="brand-mark"><b>B</b><b class="torch">R<span>●</span></b><b>D<i>••</i></b></span><span class="brand-subtitle">Building Resilience to Disasters</span></a>
      <a class="text-link" href="index.php">Back to home <span>↗</span></a>
    </header>
    <main class="research-main">
      <section class="research-hero">
        <div><p class="eyebrow">Research support desk</p><h1>Shape the question before the fieldwork.</h1><p>BRD helps students, researchers, organisations and community teams turn an early idea into a clear, ethical and practical research direction.</p></div>
        <aside class="research-card"><h2>Good research starts clearly.</h2><p>Tell us what you are exploring and where you are in the process. We can help you clarify a title, objectives, a problem statement, methods, data needs, or a resilience-focused study plan.</p></aside>
      </section>
      <section class="research-services" aria-label="BRD research support areas">
        <article class="research-service"><h3>Title and scope</h3><p>Refine a research topic so it is specific, relevant, and workable within your time and resources.</p></article>
        <article class="research-service"><h3>Objectives and methods</h3><p>Connect measurable objectives with appropriate questions, sampling, tools, and analysis.</p></article>
        <article class="research-service"><h3>Problem and evidence</h3><p>Frame the issue clearly and identify the evidence needed for practical decisions and impact.</p></article>
      </section>
      <?php if ($message !== ''): ?><p class="research-notice<?= $messageType === 'error' ? ' error' : '' ?>"><?= $esc($message) ?></p><?php endif; ?>
      <div class="research-grid">
        <section class="research-card"><h2>Request research support</h2><form class="research-form" method="post">
          <label>Full name<input type="text" name="name" value="<?= $esc($_SESSION['user']['name'] ?? '') ?>" required /></label>
          <label>Email or phone<input type="text" name="contact" value="<?= $esc($_SESSION['user']['username'] ?? '') ?>" required /></label>
          <label>Research area<select name="research_area" required><option value="">Choose an area</option><option>Disaster risk reduction</option><option>Climate resilience</option><option>Public health</option><option>Environment and sustainability</option><option>Research methods and data</option><option>Other development topic</option></select></label>
          <label>Where do you need help?<select name="support_stage" required><option value="">Choose a stage</option><option>Research title and scope</option><option>Objectives and research questions</option><option>Problem statement and justification</option><option>Methodology and study design</option><option>Data collection, GIS, or analysis</option><option>Full research planning consultation</option></select></label>
          <label>Working topic or question<input type="text" name="topic" placeholder="Example: Flood preparedness among coastal households" required /></label>
          <label>Context and expected support<textarea name="context" placeholder="Tell us what you have already done, your deadline, and the decision or outcome the research should support." required></textarea></label>
          <button class="button button-red" type="submit">Send research brief <span>→</span></button>
        </form></section>
        <aside class="research-card"><h2>What happens next</h2><ol class="research-steps"><li>BRD reviews your research brief.</li><li>We clarify the scope and practical need.</li><li>You receive a recommended next step.</li><li>The work can move into training, consultancy, or a structured project request.</li></ol><p style="margin-top:22px">This service supports ethical, evidence-led research. It is designed to strengthen your thinking and methods, not replace your own academic or organisational responsibility.</p></aside>
      </div>
    </main>
    <footer class="research-footer"><p>BRD Research Consulting Centre · Research support</p><a href="admissions.php">View all applications ↗</a></footer>
  </div>
</body>
</html>
