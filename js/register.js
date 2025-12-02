document.getElementById("registerForm").addEventListener("submit", function(e){
    e.preventDefault();
    const form = document.getElementById('registerForm');
    const countryInput = document.getElementById("country");
    
    // Validate country
    if (!countryInput.classList.contains('valid')) {
        const responseMsg = document.getElementById("responseMsg");
        responseMsg.innerHTML = '<span style="color:red;">Please enter a valid country from the suggestions.</span>';
        countryInput.focus();
        return;
    }
    
    let formData = new FormData();
    formData.append("full_name", document.getElementById("full_name").value);
    formData.append("customer_email", document.getElementById("customer_email").value);
    formData.append("password", document.getElementById("password").value);
    formData.append("confirm_password", document.getElementById("confirm_password").value);
    formData.append("city", document.getElementById("city").value);
    formData.append("country", countryInput.value.trim());
    formData.append("phone_number", document.getElementById("phone_number").value);
    const roleEl = document.querySelector('input[name="user_role"]:checked');
    formData.append("user_role", roleEl ? roleEl.value : '0');

    
    const actionUrl = form.getAttribute('action') || '../actions/register_action.php';

    fetch(actionUrl, {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(json => {
        // Use modal for better UX
        const msgModalEl = document.getElementById('messageModal');
        const msgModalBody = document.getElementById('messageModalBody');
        const msgModalTitle = document.getElementById('messageModalLabel');
        let bsModal = null;
        
        if (msgModalEl && typeof bootstrap !== 'undefined') {
            bsModal = new bootstrap.Modal(msgModalEl);
        }

        function showRegisterMessage(title, message, onClose, type = 'info') {
            if (msgModalBody) msgModalBody.textContent = message;
            if (msgModalTitle) msgModalTitle.textContent = title || 'Message';

            const modalContent = msgModalEl ? msgModalEl.querySelector('.modal-content') : null;
            const iconEl = document.getElementById('messageModalIcon');
            const okBtn = document.getElementById('messageModalOk');

            if (modalContent) {
                modalContent.classList.remove('modal-success', 'modal-error');
                if (type === 'success') modalContent.classList.add('modal-success');
                if (type === 'error') modalContent.classList.add('modal-error');
            }

            if (iconEl) {
                if (type === 'success') {
                    iconEl.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 16 16" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm3.354-8.646a.5.5 0 0 0-.708-.708L7 9.293 5.354 7.646a.5.5 0 1 0-.708.708L6.646 10l4.708-4.646z" fill="#198754"/></svg>';
                } else if (type === 'error') {
                    iconEl.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 16 16" fill="none"><path d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14z" fill="#fdecea"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" fill="#dc3545"/></svg>';
                } else {
                    iconEl.innerHTML = '';
                }
            }

            if (okBtn) {
                okBtn.classList.remove('btn-success', 'btn-danger', 'btn-primary');
                if (type === 'success') okBtn.classList.add('btn-success');
                else if (type === 'error') okBtn.classList.add('btn-danger');
                else okBtn.classList.add('btn-primary');
            }

            if (bsModal) {
                bsModal.show();
                if (okBtn) {
                    const handler = function() {
                        bsModal.hide();
                        okBtn.removeEventListener('click', handler);
                        if (typeof onClose === 'function') onClose();
                    };
                    okBtn.addEventListener('click', handler);
                }
                msgModalEl.addEventListener('hidden.bs.modal', function hiddenHandler() {
                    msgModalEl.removeEventListener('hidden.bs.modal', hiddenHandler);
                    if (typeof onClose === 'function') onClose();
                });
            } else {
                alert(message);
                if (typeof onClose === 'function') onClose();
            }
        }

        if (json.success) {
            const redirect = json.redirect || '../login/login.php';
            showRegisterMessage('Success', json.message || 'Registration successful!', function() {
                window.location.href = redirect;
            }, 'success');
        } else {
            showRegisterMessage('Registration Failed', json.message || 'Registration failed. Please try again.', null, 'error');
        }
    })
    .catch(err => {
        console.error('Registration error', err);
        const msgModalEl = document.getElementById('messageModal');
        if (msgModalEl && typeof bootstrap !== 'undefined') {
            const bsModal = new bootstrap.Modal(msgModalEl);
            document.getElementById('messageModalBody').textContent = 'An error occurred. Please try again or check console for details.';
            document.getElementById('messageModalLabel').textContent = 'Error';
            document.getElementById('messageModalIcon').innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 16 16" fill="none"><path d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14z" fill="#fdecea"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" fill="#dc3545"/></svg>';
            msgModalEl.querySelector('.modal-content').classList.add('modal-error');
            document.getElementById('messageModalOk').classList.remove('btn-primary', 'btn-success');
            document.getElementById('messageModalOk').classList.add('btn-danger');
            bsModal.show();
        } else {
            alert('An error occurred. Please try again.');
        }
    });
});
