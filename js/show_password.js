// show_password.js - attach toggle buttons to password inputs
document.addEventListener('DOMContentLoaded', function() {
  // Small inline SVG icons (eye / eye-slash)
  const EYE = '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M2.5 12s4.5-7 9.5-7 9.5 7 9.5 7-4.5 7-9.5 7-9.5-7-9.5-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
  const EYE_SLASH = '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M3 3l18 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M10.477 10.477A3 3 0 0 0 13.523 13.523" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M2.5 12s4.5-7 9.5-7c2.03 0 3.88.57 5.36 1.53" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M21.5 12s-1.77 2.76-4.66 4.63" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
  function enhancePasswordInput(input) {
    if (!input || input.dataset.toggleEnhanced) return;
    // Mark as processed to avoid duplicates
  input.dataset.toggleEnhanced = '1';

  // Create container wrapper
  const wrapper = document.createElement('div');
  wrapper.className = 'input-with-toggle';
  // Preserve parent and placement
  const parent = input.parentNode;
  parent.replaceChild(wrapper, input);
  wrapper.appendChild(input);
  // Create toggle button
  const btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'toggle-pass-btn';
  btn.setAttribute('aria-label', 'Show password');
  btn.innerHTML = EYE;
  wrapper.appendChild(btn);

  // Toggle handler
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      if (input.type === 'password') {
        btn.innerHTML = EYE_SLASH;
        btn.setAttribute('aria-label', 'Hide password');
      } else {
  input.type = 'password';
  btn.innerHTML = EYE;
  btn.setAttribute('aria-label', 'Show password');
      // Keep focus in input
      input.focus();
    });
    // Accessibility: toggle via keyboard when focused on button
    btn.addEventListener('keydown', function(ev) {
      if (ev.key === 'Enter' || ev.key === ' ') {
        ev.preventDefault();
        btn.click();
      }
    });
  }

  // Enhance all password inputs on page, but only once per input
  const passInputs = document.querySelectorAll('input[type="password"]');
  passInputs.forEach(i => enhancePasswordInput(i));
});
// show_password.js - attach toggle buttons to password inputs
document.addEventListener('DOMContentLoaded', function() {
  function attachToggle(input) {
    if (!input) return;
    // Create toggle button
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-sm btn-outline-secondary ms-2 show-pass-toggle';
    btn.innerHTML = 'Show';
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = 'Hide';
      } else {
        input.type = 'password';
        btn.innerHTML = 'Show';
      }
      input.focus();
    });

    // Insert after the input
    const wrapper = document.createElement('div');
    wrapper.className = 'd-flex align-items-center';
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);
    wrapper.appendChild(btn);
  }

  // Attach to login password
  const loginPass = document.querySelector('input[name="password"]');
  if (loginPass) attachToggle(loginPass);

  // Attach to register password fields (by IDs)
  const regPass = document.getElementById('password');
  const regConfirm = document.getElementById('confirm_password');
  if (regPass) attachToggle(regPass);
  if (regConfirm) attachToggle(regConfirm);
});
