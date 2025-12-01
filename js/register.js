document.getElementById("registerForm").addEventListener("submit", function(e){
    e.preventDefault();
    // Client-side country validation (if countries.js is loaded)
    const countryVal = document.getElementById('country') ? document.getElementById('country').value.trim() : '';
    if (typeof window.isValidCountry === 'function') {
        if (!window.isValidCountry(countryVal)) {
            const container = document.getElementById("responseMsg");
            container.innerHTML = '<span style="color:red;">Please enter a valid country.</span>';
            document.getElementById('country').focus();
            return false;
        }
    }

    const form = document.getElementById('registerForm');
    let formData = new FormData();
    formData.append("full_name", document.getElementById("full_name").value);
    formData.append("customer_email", document.getElementById("customer_email").value);
    formData.append("password", document.getElementById("password").value);
    formData.append("confirm_password", document.getElementById("confirm_password").value);
    formData.append("city", document.getElementById("city").value);
    formData.append("country", document.getElementById("country").value);
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
        const container = document.getElementById("responseMsg");
        if (json.success) {
            container.innerHTML = '<span style="color:green;">' + (json.message || 'Success') + '</span>';
            // Redirect after short delay so user sees message
            if (json.redirect) {
                setTimeout(() => {
                    window.location.href = json.redirect;
                }, 1200);
            }
        } else {
            container.innerHTML = '<span style="color:red;">' + (json.message || 'Registration failed') + '</span>';
        }
    })
    .catch(err => {
        console.error('Registration error', err);
        document.getElementById("responseMsg").innerHTML = '<span style="color:red;">An error occurred. Check console.</span>';
    });
});
