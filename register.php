<?php
$requestedRole = $_GET['role'] ?? 'client';
$role = in_array($requestedRole, ['client', 'assistant'], true) ? $requestedRole : 'client';
$roleLabel = ucfirst($role);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register as <?= $roleLabel ?> | BRD Research Consulting Centre</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    .register-page{min-height:100vh;display:grid;grid-template-columns:minmax(280px,.85fr) minmax(320px,1.15fr);background:var(--cream)}
    .register-aside{display:flex;flex-direction:column;justify-content:space-between;padding:44px clamp(28px,6vw,86px);background:var(--navy);color:#fff}.register-aside .brand{color:#fff}.register-aside .brand-subtitle{border-color:#fff}.register-aside h1{max-width:440px;margin:30px 0 16px;font:600 clamp(58px,8vw,98px)/.86 "Barlow Condensed",sans-serif;text-transform:uppercase}.register-aside p{max-width:390px;color:#d5d9e8;line-height:1.7}.register-aside .eyebrow{color:#e7bc42}.register-main{display:grid;place-items:center;padding:42px 32px}.register-card{width:min(100%,500px)}.register-card h2{margin:12px 0 10px;color:var(--navy);font:600 60px/.9 "Barlow Condensed",sans-serif;text-transform:uppercase}.register-card .intro{color:var(--muted);line-height:1.6;margin-bottom:28px}.register-role{display:flex;gap:8px;margin-bottom:24px}.register-role a{padding:10px 14px;border:1px solid #ccd0cd;color:var(--muted);font-size:12px;text-decoration:none}.register-role a.is-active{border-color:var(--red);color:var(--red);font-weight:700}.register-form label{display:block;margin:16px 0;color:var(--ink);font-size:13px;font-weight:600}.register-form input{display:block;width:100%;margin-top:7px;padding:14px;border:1px solid #c9ceca;background:#fff;color:var(--ink);font:15px "DM Sans",sans-serif}.register-form input:focus{outline:2px solid #d99899;border-color:var(--red)}.register-submit{width:100%;border:0;cursor:pointer;text-align:left;margin-top:12px}.register-submit span{float:right}.register-message{min-height:20px;margin:16px 0 0;color:var(--green);font-size:13px}.register-message.is-error{color:var(--red)}.register-footer{display:flex;justify-content:space-between;gap:16px;align-items:center;margin-top:24px;font-size:13px}.register-footer a{color:var(--navy);font-weight:700;text-decoration:none}@media(max-width:760px){.register-page{display:block}.register-aside{padding:28px 20px 40px;min-height:330px}.register-aside h1{margin-top:54px}.register-aside .brand-mark{transform:scale(.9);transform-origin:left top}.register-main{padding:42px 20px 60px}.register-card h2{font-size:54px}.register-footer{align-items:flex-start;flex-direction:column}}
  </style>
</head>
<body>
  <div class="topline"></div>
  <main class="register-page">
    <aside class="register-aside">
      <a class="brand" href="index.php" aria-label="BRD home"><span class="brand-mark"><b>B</b><b class="torch">R<span>●</span></b><b>D<i>••</i></b></span><span class="brand-subtitle">Building Resilience to Disasters</span></a>
      <div><p class="eyebrow">Join the work</p><h1>Build better answers.</h1><p>Set up your BRD workspace to follow projects, share research, and keep the next step visible.</p></div>
    </aside>
    <section class="register-main" aria-labelledby="register-title">
      <div class="register-card">
        <p class="eyebrow">BRD research portal</p>
        <h2 id="register-title">Create your account.</h2>
        <p class="intro">Choose how you work with BRD, then add your details below. Administrator accounts are created separately.</p>
        <div class="register-role" aria-label="Account type"><a class="<?= $role === 'client' ? 'is-active' : '' ?>" href="register.php?role=client">Client</a><a class="<?= $role === 'assistant' ? 'is-active' : '' ?>" href="register.php?role=assistant">Assistant</a></div>
        <form class="register-form" id="register-form">
          <input type="hidden" name="role" value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>">
          <label for="register-name">Full name<input id="register-name" name="name" type="text" autocomplete="name" placeholder="Your full name" required></label>
          <label for="register-username">Username<input id="register-username" name="username" type="text" autocomplete="username" placeholder="Choose a username" required></label>
          <label for="register-password">Password<input id="register-password" name="password" type="password" autocomplete="new-password" placeholder="Create a password" minlength="8" required></label>
          <label for="register-confirm-password">Confirm password<input id="register-confirm-password" name="confirm_password" type="password" autocomplete="new-password" placeholder="Repeat your password" minlength="8" required></label>
          <p class="register-message" id="register-message" role="status"></p>
          <button class="button button-red register-submit" type="submit">Create <?= $roleLabel ?> account <span>→</span></button>
        </form>
        <div class="register-footer"><a href="index.php">Already have an account? Sign in <span>↗</span></a><a href="index.php#contact">Need help? <span>↗</span></a></div>
      </div>
    </section>
  </main>
  <script>
    const registerForm = document.querySelector('#register-form');
    const registerMessage = document.querySelector('#register-message');
    registerForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      const formData = new FormData(registerForm);
      const payload = Object.fromEntries(formData.entries());
      payload.action = 'register';
      if (payload.password !== payload.confirm_password) {
        registerMessage.textContent = 'Passwords do not match.';
        registerMessage.classList.add('is-error');
        return;
      }
      registerMessage.classList.remove('is-error');
      registerMessage.textContent = 'Creating your account...';
      try {
        const response = await fetch('auth.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Registration failed.');
        window.location.href = result.redirect || 'dashboard.php';
      } catch (error) {
        registerMessage.textContent = error.message === 'Failed to fetch' ? 'Start the PHP server to register.' : error.message;
        registerMessage.classList.add('is-error');
      }
    });
  </script>
</body>
</html>
