
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start();


/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn()
{
    return isset($_SESSION['id']); // Giving true if session exists
}

/**
 * Alias for isLoggedIn() for compatibility
 * @return bool
 */
function is_logged_in()
{
    return isLoggedIn();
}

/**
 * Get user ID from session
 * @return int|null
 */
function get_user_id()
{
    return isset($_SESSION['id']) ? $_SESSION['id'] : null;
}

/**
 * Get user name from session
 * @return string
 */
function get_user_name()
{
    return isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User';
}

/**
 * Check if user has a specific role
 * @param int $role
 * @return bool
 */
function check_user_role($role)
{
    return isset($_SESSION['role']) && $_SESSION['role'] == $role;
}

/**
 * Check if user is admin
 * @return bool
 */
function isAdmin()
{
    // Accept both 2 (new admin) and 1 (legacy admin) while we verify role mapping
    return (isset($_SESSION['role']) && ($_SESSION['role'] == 2 || $_SESSION['role'] == 1));
}
?>
