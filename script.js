const menuButton = document.querySelector('.menu-button');
const nav = document.querySelector('.nav');
menuButton?.addEventListener('click', () => {
  const isOpen = nav.classList.toggle('is-open');
  menuButton.setAttribute('aria-expanded', isOpen);
});
document.querySelectorAll('.nav a').forEach((link) => link.addEventListener('click', () => {
  nav.classList.remove('is-open');
  menuButton?.setAttribute('aria-expanded', 'false');
}));
document.querySelector('#year').textContent = new Date().getFullYear();

const authModal = document.querySelector('#auth-modal');
const authForm = document.querySelector('#auth-form');
const roleInput = document.querySelector('#auth-role');
const modeButtons = document.querySelectorAll('.mode-button');
const roleTabs = document.querySelectorAll('.role-tab');
const nameField = document.querySelector('.name-field');
const authNote = document.querySelector('#auth-note');
const authMessage = document.querySelector('#auth-message');
const authSubmit = document.querySelector('.auth-submit');
const registerPageLink = document.querySelector('#register-page-link');
const confirmPasswordField = document.querySelector('#auth-confirm-password');

let authMode = 'login';

function openAuth() {
  authModal.hidden = false;
  authModal.classList.add('is-open');
  document.body.classList.add('modal-open');
  document.querySelector('#auth-username').focus();
}

function closeAuth() {
  authModal.classList.remove('is-open');
  authModal.hidden = true;
  document.body.classList.remove('modal-open');
  authMessage.textContent = '';
}

function setRole(role) {
  roleInput.value = role;
  registerPageLink.href = `register.php?role=${role}`;
  roleTabs.forEach((tab) => {
    const selected = tab.dataset.role === role;
    tab.classList.toggle('is-active', selected);
    tab.setAttribute('aria-selected', selected);
  });
  const admin = role === 'admin';
  if (admin) authMode = 'login';
  document.querySelector('.auth-mode').classList.toggle('is-hidden', admin);
  registerPageLink.classList.toggle('is-hidden', admin);
  nameField.classList.toggle('is-hidden', authMode === 'login');
  confirmPasswordField.closest('label').classList.toggle('is-hidden', authMode === 'login');
  document.querySelector('#auth-name').required = authMode === 'register';
  confirmPasswordField.required = authMode === 'register';
  authNote.textContent = admin ? 'Admin access is limited to the BRD administrator.' : `Create or use your ${role} portal account.`;
  authSubmit.innerHTML = `${authMode === 'login' ? 'Sign in' : 'Create account'} <span>→</span>`;
  authMessage.textContent = '';
}

function setMode(mode) {
  authMode = mode;
  modeButtons.forEach((button) => button.classList.toggle('is-active', button.dataset.mode === mode));
  nameField.classList.toggle('is-hidden', mode === 'login');
  confirmPasswordField.closest('label').classList.toggle('is-hidden', mode === 'login');
  document.querySelector('#auth-name').required = mode === 'register';
  confirmPasswordField.required = mode === 'register';
  authSubmit.innerHTML = `${mode === 'login' ? 'Sign in' : 'Create account'} <span>→</span>`;
  authMessage.textContent = '';
}

document.querySelectorAll('[data-open-auth]').forEach((button) => button.addEventListener('click', openAuth));
document.querySelectorAll('[data-close-auth]').forEach((button) => button.addEventListener('click', closeAuth));
roleTabs.forEach((tab) => tab.addEventListener('click', () => setRole(tab.dataset.role)));
modeButtons.forEach((button) => button.addEventListener('click', () => setMode(button.dataset.mode)));

authForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  const role = roleInput.value;
  const username = document.querySelector('#auth-username').value.trim();
  const password = document.querySelector('#auth-password').value;
  const name = document.querySelector('#auth-name').value.trim();
  const confirmPassword = confirmPasswordField.value;
  authSubmit.disabled = true;
  authMessage.textContent = 'Connecting to the BRD portal...';
  authMessage.classList.remove('is-error');

  try {
    const response = await fetch('auth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: authMode, role, name, username, password, confirm_password: confirmPassword })
    });
    const result = await response.json();
    if (!response.ok || !result.success) throw new Error(result.message || 'Authentication failed.');
    window.location.href = result.redirect || 'confirm.php';
  } catch (error) {
    authMessage.textContent = error.message === 'Failed to fetch' ? 'Start the PHP server to use the portal.' : error.message;
    authMessage.classList.add('is-error');
  } finally {
    authSubmit.disabled = false;
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && authModal.classList.contains('is-open')) closeAuth();
});

const feedGrid = document.querySelector('#public-feed-grid');
if (feedGrid) {
  const escapeHtml = (value) => String(value).replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character]);
  const dots = document.querySelector('#slider-dots');
  let currentSlide = 0;
  let slideTimer;

  const showSlide = (index) => {
    const slides = [...feedGrid.children];
    if (!slides.length) return;
    currentSlide = (index + slides.length) % slides.length;
    slides.forEach((slide, slideIndex) => slide.classList.toggle('is-current', slideIndex === currentSlide));
    if (dots) [...dots.children].forEach((dot, dotIndex) => dot.classList.toggle('is-current', dotIndex === currentSlide));
  };

  const startSlider = () => {
    window.clearInterval(slideTimer);
    slideTimer = window.setInterval(() => showSlide(currentSlide + 1), 6000);
  };

  document.querySelector('.slider-prev')?.addEventListener('click', () => { showSlide(currentSlide - 1); startSlider(); });
  document.querySelector('.slider-next')?.addEventListener('click', () => { showSlide(currentSlide + 1); startSlider(); });

  fetch('public_content.php').then((response) => response.json()).then((result) => {
    if (!result.success || !result.items.length) {
      feedGrid.innerHTML = '<p class="feed-empty">New opportunities will appear here when BRD publishes them.</p>';
      return;
    }
    feedGrid.innerHTML = result.items.map((item) => {
      const title = escapeHtml(item.title || 'BRD update');
      const details = escapeHtml(item.details || '');
      const media = item.image_path ? `<img src="${escapeHtml(item.image_path)}" alt="${title} published image">` : item.video_path ? `<video controls preload="metadata"><source src="${escapeHtml(item.video_path)}"></video>` : '';
      const action = item.type === 'course' ? '<a href="#contact" class="feed-link">Ask about admission <span>→</span></a>' : item.type === 'consultancy' ? '<a href="#contact" class="feed-link">Ask about consultancy <span>→</span></a>' : '';
      const label = item.type === 'course' ? 'Research training' : item.type === 'consultancy' ? 'Consultancy service' : 'BRD update';
      return `<article class="public-feed-card">${media}<p class="eyebrow">${label}</p><h3>${title}</h3><p>${details}</p>${action}</article>`;
    }).join('');
    if (dots) {
      dots.innerHTML = result.items.map((_, index) => `<button type="button" aria-label="Show opportunity ${index + 1}"></button>`).join('');
      [...dots.children].forEach((dot, index) => dot.addEventListener('click', () => { showSlide(index); startSlider(); }));
    }
    showSlide(0);
    startSlider();
  }).catch(() => { feedGrid.innerHTML = '<p class="feed-empty">Start the PHP server to load current BRD opportunities.</p>'; });
}