<?php
session_start();
require_once __DIR__ . DIRECTORY_SEPARATOR . 'db.php';

$database = db();
$esc = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = trim((string) ($_POST['request_type'] ?? 'admission'));
    $subject = trim((string) ($_POST['subject'] ?? ''));
    $messageText = trim((string) ($_POST['message'] ?? ''));
    $name = trim((string) ($_POST['applicant_name'] ?? $_SESSION['user']['name'] ?? ''));
    $username = trim((string) ($_SESSION['user']['username'] ?? 'guest'));
    $email = trim((string) ($_POST['applicant_email'] ?? ''));
    $phone = trim((string) ($_POST['applicant_phone'] ?? ''));
    $courseName = trim((string) ($_POST['course_name'] ?? ''));
    $duration = trim((string) ($_POST['duration'] ?? ''));
  $allowedTypes = ['admission', 'training', 'consultancy', 'feedback'];

  if (!in_array($type, $allowedTypes, true) || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || $courseName === '' || $duration === '' || $subject === '' || $messageText === '') {
    $message = 'Please complete the applicant, programme, and contact details before submitting.';
    } else {
    $statement = $database->prepare('INSERT INTO requests (id, name, username, type, subject, message, status, payment_status, created_at, email, phone, course_name, duration) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $statement->execute([
            uniqid('brd_', true),
            $name,
            $username,
            $type,
            $subject,
            $messageText,
            'Submitted',
            $type === 'admission' ? 'Pending' : 'Not applicable',
            date('Y-m-d H:i:s'),
            $email,
            $phone,
            $courseName,
            $duration
        ]);
        $message = 'Your BRD admission/application was submitted successfully. The administration team will review it soon.';
    }
}

$content = [
  'announcements' => $database->query("SELECT title, details AS body FROM content WHERE type = 'announcement' ORDER BY id DESC LIMIT 3")->fetchAll(),
  'courses' => $database->query("SELECT title, details FROM content WHERE type = 'course' ORDER BY id DESC LIMIT 4")->fetchAll(),
  'consultancy' => $database->query("SELECT title, details FROM content WHERE type = 'consultancy' ORDER BY id DESC LIMIT 4")->fetchAll()
];

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BRD Admissions | Building Resilience to Disasters</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="styles.css" />
  <style>
    .admissions-shell { min-height: 100vh; background: var(--cream); }
    .admissions-header, .admissions-main, .admissions-footer { max-width: 1240px; margin: auto; }
    .admissions-header { padding: 26px 32px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #d9dcd7; }
    .admissions-main { padding: 56px 32px 80px; }
    .admissions-hero { display: grid; grid-template-columns: 1.1fr .9fr; gap: 26px; align-items: center; margin-bottom: 34px; }
    .admissions-hero h1 { margin: 10px 0 14px; color: var(--navy); font: 600 clamp(52px, 6vw, 84px)/.9 "Barlow Condensed", sans-serif; text-transform: uppercase; }
    .admissions-hero p { color: var(--muted); line-height: 1.7; }
    .admissions-panel { background: #fff; padding: 30px; border-top: 4px solid var(--navy); }
    .admissions-grid { display: grid; grid-template-columns: 1.1fr .9fr; gap: 22px; }
    .admissions-form label { display: block; margin: 14px 0; font-size: 12px; font-weight: 700; }
    .admissions-form input, .admissions-form select, .admissions-form textarea { display: block; width: 100%; margin-top: 8px; padding: 12px; border: 1px solid #c9ceca; background: #fff; font: 13px "DM Sans", sans-serif; }
    .admissions-form textarea { min-height: 120px; resize: vertical; }
    .admissions-form button { margin-top: 10px; border: 0; cursor: pointer; }
    .admissions-message { margin: 0 0 22px; padding: 14px 18px; background: #e6f0e8; border-left: 4px solid var(--green); color: var(--green); font-size: 13px; }
    .admissions-list { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 20px; }
    .admissions-card { padding: 22px; background: var(--cream); border-left: 3px solid var(--red); }
    .admissions-card h3 { margin: 0 0 10px; color: var(--navy); font-size: 18px; }
    .admissions-card p { margin: 0; color: var(--muted); line-height: 1.6; font-size: 13px; }
    .admissions-footer { padding: 0 32px 28px; display: flex; justify-content: space-between; color: var(--muted); font-size: 12px; }
    .admissions-footer a { color: var(--navy); font-weight: 700; text-decoration: none; }
    @media (max-width: 760px) {
      .admissions-header, .admissions-main, .admissions-footer { padding-left: 20px; padding-right: 20px; }
      .admissions-hero, .admissions-grid, .admissions-list { grid-template-columns: 1fr; }
      .admissions-footer { display: block; }
    }
  </style>
</head>
<body>
  <div class="topline"></div>
  <div class="admissions-shell">
    <header class="admissions-header">
      <a class="brand" href="index.php" aria-label="BRD home"><span class="brand-mark"><b>B</b><b class="torch">R<span>●</span></b><b>D<i>••</i></b></span><span class="brand-subtitle">Building Resilience to Disasters</span></a>
      <a class="text-link" href="index.php">Back to home <span>↗</span></a>
    </header>

    <main class="admissions-main">
      <section class="admissions-hero">
        <div>
          <p class="eyebrow">Admissions & application</p>
          <h1>Submit your BRD application.</h1>
          <p>Apply for BRD admission, training opportunities, consultancy services, or general support through a simple, structured process. Each request is reviewed by the BRD administration team.</p>
        </div>
        <div class="admissions-panel">
          <h2>How it works</h2>
          <ol style="padding-left: 18px; color: var(--muted); line-height: 1.9;">
            <li>Choose the relevant BRD category.</li>
            <li>Fill in the application details.</li>
            <li>Submit your request for review.</li>
            <li>Receive an outcome from the BRD team.</li>
          </ol>
        </div>
      </section>

      <?php if ($message !== ''): ?>
        <p class="admissions-message"><?= $esc($message) ?></p>
      <?php endif; ?>

      <div class="admissions-grid">
        <section class="admissions-panel">
          <h2>Application form</h2>
          <form class="admissions-form" method="POST">
            <label>Application type
              <select name="request_type" required>
                <option value="admission">BRD Admission</option>
                <option value="training">Training Application</option>
                <option value="consultancy">Consultancy Request</option>
                <option value="feedback">General Inquiry</option>
              </select>
            </label>
            <label>Applicant full name
              <input type="text" name="applicant_name" value="<?= $esc($_SESSION['user']['name'] ?? '') ?>" required />
            </label>
            <label>Email address
              <input type="email" name="applicant_email" required placeholder="applicant@example.com" />
            </label>
            <label>Phone number
              <input type="text" name="applicant_phone" required placeholder="+255 ..." />
            </label>
            <label>Course or programme
              <input type="text" name="course_name" required placeholder="Example: Disaster Risk Reduction Certificate" />
            </label>
            <label>Admission duration
              <select name="duration" required><option value="">Choose duration</option><option>Three months</option><option>Six months</option><option>One year</option><option>Short professional programme</option></select>
            </label>
            <label>Subject
              <input type="text" name="subject" placeholder="Example: Research Methodology Training" required />
            </label>
            <label>Application details
              <textarea name="message" placeholder="Describe your request, background, area of interest, or support needed." required></textarea>
            </label>
            <button class="button button-red" type="submit">Submit application <span>→</span></button>
          </form>
        </section>

        <aside class="admissions-panel">
          <h2>Key BRD areas</h2>
          <div class="admissions-list">
            <article class="admissions-card">
              <h3>Research</h3>
              <p>Methodology, surveys, data analysis, GIS and evidence-based project support.</p>
            </article>
            <article class="admissions-card">
              <h3>Training</h3>
              <p>Capacity building in research, climate resilience and disaster preparedness.</p>
            </article>
            <article class="admissions-card">
              <h3>Consultancy</h3>
              <p>Risk assessment, planning, technical guidance, and resilience-focused advisory work.</p>
            </article>
          </div>
        </aside>
      </div>

      <section class="admissions-panel" style="margin-top: 24px;">
        <h2>Open opportunities</h2>
        <div class="admissions-list">
          <?php foreach ($content['courses'] as $course): ?>
            <article class="admissions-card">
              <h3><?= $esc($course['title'] ?? '') ?></h3>
              <p><?= $esc($course['details'] ?? '') ?></p>
            </article>
          <?php endforeach; ?>
          <?php foreach ($content['consultancy'] as $service): ?>
            <article class="admissions-card">
              <h3><?= $esc($service['title'] ?? '') ?></h3>
              <p><?= $esc($service['details'] ?? '') ?></p>
            </article>
          <?php endforeach; ?>
          <?php if (empty($content['courses']) && empty($content['consultancy'])): ?>
            <article class="admissions-card">
              <h3>No active opportunities</h3>
              <p>New BRD training, consultancy, and admission opportunities will appear here as they are published.</p>
            </article>
          <?php endif; ?>
        </div>
      </section>
    </main>

    <footer class="admissions-footer">
      <p>BRD Research Consulting Centre · Admissions portal</p>
      <a href="index.php#contact">Need support? Contact BRD ↗</a>
    </footer>
  </div>
</body>
</html>
