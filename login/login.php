<?php
// login.php - User Login Page for EasyBuy
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EasyBuy</title>
    <link rel="stylesheet" href="../css/styles.css">
    <!-- Bootstrap CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="logo">EasyBuy</div>
        <nav class="nav">
            <a href="../index.php">Home</a>
            <a href="register.php" class="btn-primary">Register</a>
        </nav>
    </header>

    <section class="form-section">
        <div class="form-container">
            <h2>Login</h2>
            <form action="../actions/login_action.php" method="POST">
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" class="btn-primary">Login</button>
            </form>
            <p class="alt-link">Don't have an account? <a href="register.php">Register here</a></p>
            <p class="alt-link">Forgot password? <a href="../view/forgot_password.php">Reset password here</a></p>
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

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
            <script src="../js/login.js"></script>
            <script src="../js/show_password.js"></script>
</body>
</html>
