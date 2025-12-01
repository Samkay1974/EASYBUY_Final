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
