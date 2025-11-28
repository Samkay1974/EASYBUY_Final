<?php
// logout.php - Logs the user out and destroys the session
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect user back to login page
header("Location: ../index.php");
exit();
?>