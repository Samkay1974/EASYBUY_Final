// show_password.js - attach toggle buttons to password inputs
document.addEventListener('DOMContentLoaded', function() {
  // Enhance password inputs with a single, non-duplicating toggle button
  function enhancePasswordInput(input) {
    if (!input || input.dataset.showToggleAttached === '1') return;

    // Mark as attached to avoid duplicates
    input.dataset.showToggleAttached = '1';

    // Prefer to use Bootstrap input-group if available
    const parent = input.parentNode;
    let group = parent.classList && parent.classList.contains('input-group') ? parent : null;

    if (!group) {
      // Create input-group and replace input
      group = document.createElement('div');
      group.className = 'input-group';
      parent.replaceChild(group, input);
      group.appendChild(input);
    }

    // If a toggle already exists next to this input, skip
    if (group.querySelector('.show-pass-toggle')) return;

    // Create button wrapper and button
    const btnWrapper = document.createElement('button');
    btnWrapper.type = 'button';
    btnWrapper.className = 'btn btn-outline-secondary show-pass-toggle';
    btnWrapper.title = 'Show password';
    btnWrapper.setAttribute('aria-label', 'Toggle password visibility');
    btnWrapper.innerHTML = '<i class="fa fa-eye"></i>';

    btnWrapper.addEventListener('click', function(e) {
      e.preventDefault();
      if (input.type === 'password') {
        input.type = 'text';
        btnWrapper.innerHTML = '<i class="fa fa-eye-slash"></i>';
        btnWrapper.title = 'Hide password';
      } else {
        input.type = 'password';
        btnWrapper.innerHTML = '<i class="fa fa-eye"></i>';
        btnWrapper.title = 'Show password';
      }
      input.focus();
    });

    // Create input-group-append container if not present (Bootstrap 5 uses input-group-append style via wrapper)
    const appendSpan = document.createElement('span');
    appendSpan.className = 'input-group-text p-0';
    appendSpan.style.border = '0';
    appendSpan.appendChild(btnWrapper);

    // Append the toggle to the input group
    group.appendChild(appendSpan);
  }

  // Find password fields by common selectors
  const selectors = [
    'input[type="password"]',
    'input[name="password"]',
    '#password',
    '#confirm_password'
  ];

  const seen = new Set();
  selectors.forEach(sel => {
    document.querySelectorAll(sel).forEach(input => {
      // Avoid enhancing the same element multiple times
      if (!seen.has(input)) {
        enhancePasswordInput(input);
        seen.add(input);
      }
    });
  });
});
