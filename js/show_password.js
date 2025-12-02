// show_password.js - Checkbox-style password visibility toggle
document.addEventListener('DOMContentLoaded', function() {
  
  function addPasswordToggle(input) {
    // Skip if already enhanced
    if (!input || input.dataset.passwordToggle === 'added') return;
    input.dataset.passwordToggle = 'added';

    // Get parent container
    const parent = input.parentNode;
    
    // Skip if toggle checkbox already exists
    if (parent.querySelector('.show-password-checkbox')) return;

    // Create checkbox wrapper
    const checkboxWrapper = document.createElement('div');
    checkboxWrapper.className = 'show-password-wrapper';
    
    // Create checkbox
    const checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.id = 'show-password-' + Math.random().toString(36).substr(2, 9);
    checkbox.className = 'show-password-checkbox';
    
    // Create label for checkbox
    const checkboxLabel = document.createElement('label');
    checkboxLabel.htmlFor = checkbox.id;
    checkboxLabel.className = 'show-password-label';
    checkboxLabel.innerHTML = '<span class="checkbox-tick">✓</span> Show Password';
    
    // Append checkbox and label
    checkboxWrapper.appendChild(checkbox);
    checkboxWrapper.appendChild(checkboxLabel);
    
    // Toggle functionality
    checkbox.addEventListener('change', function() {
      if (checkbox.checked) {
        input.type = 'text';
        checkboxLabel.classList.add('checked');
      } else {
        input.type = 'password';
        checkboxLabel.classList.remove('checked');
      }
    });

    // Add wrapper after input
    parent.appendChild(checkboxWrapper);
  }

  // Find all password inputs
  const passwordInputs = document.querySelectorAll('input[type="password"]');
  passwordInputs.forEach(addPasswordToggle);

  // Watch for dynamically added password fields
  const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      mutation.addedNodes.forEach(function(node) {
        if (node.nodeType === 1) {
          if (node.tagName === 'INPUT' && node.type === 'password') {
            addPasswordToggle(node);
          }
          const passwordInputs = node.querySelectorAll && node.querySelectorAll('input[type="password"]');
          if (passwordInputs) {
            passwordInputs.forEach(addPasswordToggle);
          }
        }
      });
    });
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true
  });
});

