<?php
session_start();
require_once __DIR__ . DIRECTORY_SEPARATOR . 'db.php';
$database = db();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $skills = trim((string) ($_POST['skills'] ?? ''));
    $interest = trim((string) ($_POST['interest'] ?? ''));

    if ($name === '' || $email === '' || $phone === '' || $skills === '') {
        $message = 'Please fill in all required volunteer details before submitting.';
    } else {
        $statement = $database->prepare('INSERT INTO requests (id, name, username, type, subject, message, status, payment_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $statement->execute([
            uniqid('brd_volunteer_', true),
            $name,
            $email,
            'volunteer',
            'Volunteer Application',
            "Email: {$email}\nPhone: {$phone}\nSkills: {$skills}\nArea of interest: {$interest}",
            'Submitted',
            'Not applicable',
            date('Y-m-d H:i:s')
        ]);
        $message = 'Your volunteer application has been submitted successfully. BRD will review it soon.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Volunteer with BRD</title>
  <link rel="stylesheet" href="styles.css" />
  <style>
    .page-shell { min-height: 100vh; background: var(--cream); }
    .page-header, .page-main, .page-footer { max-width: 1240px; margin: auto; }
    .page-header { display: flex; justify-content: space-between; align-items: center; padding: 26px 32px; border-bottom: 1px solid #d9dcd7; }
    .page-main { padding: 56px 32px 80px; }
    .page-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; }
    .page-panel { background: #fff; padding: 30px; border-top: 4px solid var(--green); }
    .page-panel h1, .page-panel h2 { margin: 0 0 16px; color: var(--navy); font: 600 clamp(42px, 5vw, 72px)/.9 "Barlow Condensed", sans-serif; text-transform: uppercase; }
    .page-panel p { color: var(--muted); line-height: 1.7; }
    .volunteer-form label { display: block; margin: 14px 0; font-size: 12px; font-weight: 700; }
    .volunteer-form input, .volunteer-form textarea { display: block; width: 100%; margin-top: 8px; padding: 12px; border: 1px solid #c9ceca; font: 13px "DM Sans", sans-serif; }
    .volunteer-form textarea { min-height: 110px; resize: vertical; }
    .volunteer-form button { border: 0; margin-top: 10px; cursor: pointer; }
    .notice { margin: 0 0 22px; padding: 14px 18px; background: #e6f0e8; border-left: 4px solid var(--green); color: var(--green); font-size: 13px; }
    .page-footer { padding: 0 32px 28px; display: flex; justify-content: space-between; color: var(--muted); font-size: 12px; }
    .page-footer a { color: var(--navy); text-decoration: none; font-weight: 700; }
    @media (max-width: 760px) { .page-header, .page-main, .page-footer { padding-left: 20px; padding-right: 20px; } .page-grid { grid-template-columns: 1fr; } .page-footer { display: block; } }
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
      <?php if ($message !== ''): ?><p class="notice"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
      <div class="page-grid">
        <section class="page-panel">
          <h1>Join the BRD volunteer network.</h1>
          <p>BRD welcomes volunteers who are committed to disaster risk reduction, climate resilience, research, data collection, outreach and community support.</p>
          <p>Volunteering with BRD is a practical way to contribute to safer communities, stronger evidence, and timely action during crises.</p>
        </section>
        <section class="page-panel">
          <h2>Volunteer form</h2>
          <form class="volunteer-form" method="POST">
            <label>Full name<input type="text" name="name" placeholder="Your full name" required /></label>
            <label>Email<input type="email" name="email" placeholder="you@example.com" required /></label>
            <label>Phone number<input type="text" name="phone" placeholder="+255 ..." required /></label>
            <label>Skills or expertise<textarea name="skills" placeholder="Data collection, GIS, community outreach, first aid, research..." required></textarea></label>
            <label>Area of interest<textarea name="interest" placeholder="Disaster response, climate resilience, research, training, communications, etc."></textarea></label>
            <button class="button button-red" type="submit">Submit volunteer request <span>→</span></button>
          </form>
        </section>
      </div>
    </main>
    <footer class="page-footer">
      <p>BRD Research Consulting Centre · Volunteer recruitment</p>
      <a href="admissions.php">Apply for BRD services ↗</a>
    </footer>
  </div>
</body>
</html>
