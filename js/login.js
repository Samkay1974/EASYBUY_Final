document.addEventListener('DOMContentLoaded', function(){
    const form = document.querySelector('form[action="../actions/login_action.php"]');
    if (!form) return;

    // Bootstrap modal elements (assumes modal markup exists in the page)
    const msgModalEl = document.getElementById('messageModal');
    const msgModalBody = document.getElementById('messageModalBody');
    const msgModalTitle = document.getElementById('messageModalLabel');
    let bsModal = null;
    if (msgModalEl && typeof bootstrap !== 'undefined') {
        bsModal = new bootstrap.Modal(msgModalEl);
    }

    function showMessage(title, message, onClose, type = 'info') {
        if (msgModalBody) msgModalBody.textContent = message;
        if (msgModalTitle) msgModalTitle.textContent = title || 'Message';

        // determine classes and icon
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
            // OK button should hide and then call onClose
            if (okBtn) {
                const handler = function() {
                    bsModal.hide();
                    okBtn.removeEventListener('click', handler);
                    if (typeof onClose === 'function') onClose();
                };
                okBtn.addEventListener('click', handler);
            }
            // Also redirect if user closes modal via backdrop/close button
            msgModalEl.addEventListener('hidden.bs.modal', function hiddenHandler() {
                msgModalEl.removeEventListener('hidden.bs.modal', hiddenHandler);
                if (typeof onClose === 'function') onClose();
            });
        } else {
            // Fallback - still use alert
            alert(message);
            if (typeof onClose === 'function') onClose();
        }
    }

    form.addEventListener('submit', function(e){
        e.preventDefault();
        const formData = new FormData(form);
        const action = form.getAttribute('action');

        fetch(action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => {
                const ct = res.headers.get('content-type') || '';
                if (ct.indexOf('application/json') !== -1) {
                    return res.json();
                }
                return res.text().then(text => { throw new Error('Non-JSON response from server: ' + text); });
            })
            .then(json => {
                if (json.success) {
                    // show modal message then redirect
                    const redirect = json.redirect || '../view/homepage.php';
                    showMessage('Success', json.message || 'Login successful', function() {
                        window.location.href = redirect;
                    }, 'success');
                } else {
                    showMessage('Login Failed', json.message || 'Invalid credentials', null, 'error');
                }
            })
            .catch(err => {
                console.error('Login error', err);
                showMessage('Error', 'An error occurred during login. Check console for details.', null, 'error');
            });
    });
});
