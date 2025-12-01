// show_password.js - Simple and clean password visibility toggle
document.addEventListener('DOMContentLoaded', function() {
  
  function addPasswordToggle(input) {
    // Skip if already enhanced or input doesn't exist
    if (!input || input.dataset.passwordToggle === 'added') return;
    input.dataset.passwordToggle = 'added';

    // Skip if toggle button already exists
    if (input.parentNode.querySelector('.password-toggle-btn')) return;

    // Create simple toggle button
    const toggleBtn = document.createElement('button');
    toggleBtn.type = 'button';
    toggleBtn.className = 'password-toggle-btn';
    toggleBtn.setAttribute('aria-label', 'Toggle password visibility');
    toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';

    // Toggle functionality
    toggleBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      if (input.type === 'password') {
        input.type = 'text';
        toggleBtn.innerHTML = '<i class="fas fa-eye-slash"></i>';
        toggleBtn.setAttribute('aria-label', 'Hide password');
      } else {
        input.type = 'password';
        toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
        toggleBtn.setAttribute('aria-label', 'Show password');
      }
      
      // Keep focus on input
      input.focus();
    });

    // Make input container relative for absolute positioning
    const parent = input.parentNode;
    const style = window.getComputedStyle(parent);
    if (style.position === 'static') {
      parent.style.position = 'relative';
    }
    
    // Ensure input has right padding for button
    input.style.paddingRight = '40px';

    // Add button to parent container
    parent.appendChild(toggleBtn);
  }

  // Find all password inputs and add toggle
  const passwordInputs = document.querySelectorAll('input[type="password"]');
  passwordInputs.forEach(addPasswordToggle);
});
