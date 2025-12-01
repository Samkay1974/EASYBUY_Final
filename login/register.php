<?php
// register.php - User Registration Page for EasyBuy
session_start();
// Retrieve and clear flash messages (if any)
$error = '';
$success = '';
if (!empty($_SESSION['register_error'])) {
    $error = $_SESSION['register_error'];
    unset($_SESSION['register_error']);
}
if (!empty($_SESSION['register_success'])) {
    $success = $_SESSION['register_success'];
    unset($_SESSION['register_success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - EasyBuy</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <header class="header">
        <div class="logo">EasyBuy</div>
        <nav class="nav">
            <a href="../index.php">Home</a>
            <a href="login.php" class="btn-primary">Login</a>
        </nav>
    </header>

    <section class="form-section">
        <div class="form-container">
            <h2>Create an Account</h2>
            <form id="registerForm" action="../actions/register_action.php" method="POST">

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <div class="input-group">
                    <label>Full Name</label>
                    <input id="full_name" type="text" name="full_name" required>
                </div>

                <div class="input-group">
                    <label>Email</label>
                    <input id="customer_email" type="email" name="customer_email" required>
                </div>

                <div class="input-group">
                    <label>City</label>
                    <input id="city" type="text" name="city" required>
                </div>

                <div class="input-group">
                    <label>Country</label>
                    <input id="country" type="text" name="country" required>
                </div>

                <div class="input-group">
                    <label>Phone number</label>
                    <input id="phone_number" type="text" name="phone_number" required>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input id="password" type="password" name="password" required>
                </div>

                <div class="input-group">
                    <label>Confirm Password</label>
                    <input id="confirm_password" type="password" name="confirm_password" required>
                </div>

                <div class="input-group">
                    <label>User type</label>
                    <div class="radio-group">
                        <label><input type="radio" name="user_role" value="0" checked required> Customer</label>
                        <label><input type="radio" name="user_role" value="1"> Wholesaler</label>
                    </div>
                </div>

                <div id="responseMsg" class="input-group"></div>

                <button type="submit" class="btn-primary">Register</button>
            </form>
            <p class="alt-link">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </section>

    <footer class="footer">
        <p>© 2025 EasyBuy. All rights reserved.</p>
    </footer>
    <script src="../js/register.js"></script>
    <script src="../js/countries.js"></script>
    <script src="../js/show_password.js"></script>
    <script>
    // Client-side validation: ensure country exists before submit
    document.getElementById('registerForm').addEventListener('submit', function(e){
        const countryInput = document.getElementById('country');
        if (countryInput) {
            const val = countryInput.value.trim();
            if (!window.isValidCountry(val)) {
                e.preventDefault();
                const container = document.getElementById('responseMsg');
                container.innerHTML = '<span style="color:red;">Please enter a valid country name.</span>';
                countryInput.focus();
                return false;
            }
        }
    });
    </script>
</body>
</html>