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
