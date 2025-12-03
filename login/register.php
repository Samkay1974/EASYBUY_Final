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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <div class="form-container register-form-container">
            <h2>Create an Account</h2>
            <form id="registerForm" action="../actions/register_action.php" method="POST">

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <div class="form-field">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input id="full_name" type="text" name="full_name" class="form-input" placeholder="Enter your full name" required>
                </div>

                <div class="form-field">
                    <label for="customer_email" class="form-label">Email</label>
                    <input id="customer_email" type="email" name="customer_email" class="form-input" placeholder="Enter your email" required>
                </div>

                <div class="form-field">
                    <label for="city" class="form-label">City</label>
                    <input id="city" type="text" name="city" class="form-input" placeholder="Enter your city" required>
                </div>

                <div class="form-field">
                    <label for="country" class="form-label">Country</label>
                    <input id="country" type="text" name="country" class="form-input" placeholder="Enter your country" required autocomplete="off">
                    
                </div>

                <div class="form-field">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input id="phone_number" type="text" name="phone_number" class="form-input" placeholder="Enter your phone number" required>
                </div>

                <div class="form-field">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password" name="password" class="form-input" placeholder="Create a password" required>
                </div>

                <div class="form-field">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input id="confirm_password" type="password" name="confirm_password" class="form-input" placeholder="Confirm your password" required>
                </div>
                <div class="form-field">
                    <label class="show-password-wrapper" for="showPassword">
                        <input type="checkbox" id="showPassword" class="show-password-checkbox" onchange="togglePassword()">
                        <span class="checkbox-tick">✓</span>
                        <span class="show-password-text">Show Passwords</span>
                    </label>
                </div>

                <div class="form-field">
                    <label class="form-label">User Type</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="user_role" value="0" checked required>
                            <span>Customer</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="user_role" value="1">
                            <span>Wholesaler</span>
                        </label>
                    </div>
                </div>

                <div id="responseMsg" class="form-field"></div>

                <button type="submit" class="btn-primary">Register</button>
            </form>
            <p class="alt-link">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </section>

    <footer class="footer">
        <p>© 2025 EasyBuy. All rights reserved.</p>
    </footer>
    
    <!-- Message Modal (used for both success and error) -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-header align-items-center" id="messageModalHeader">
                    <div id="messageModalIcon" class="me-2"></div>
                    <h5 class="modal-title" id="messageModalLabel">Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4" id="messageModalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="messageModalOk">OK</button>
                </div>
            </div>
        </div>
    </div>
<script>
        function togglePassword() {
    let pwd = document.getElementById("password");
    let confirmPwd = document.getElementById("confirm_password");
    if (pwd) {
        pwd.type = pwd.type === "password" ? "text" : "password";
    }
    if (confirmPwd) {
        confirmPwd.type = confirmPwd.type === "password" ? "text" : "password";
    }
}
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/register.js"></script>
    <script src="../js/countries.js"></script>
    
</body>
</html>