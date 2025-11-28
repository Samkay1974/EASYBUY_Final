<?php
/**
 * Expire Old Collaborations Script
 * 
 * This script should be run via cron job daily to expire collaborations
 * that have passed their expiration date.
 * 
 * Cron example (runs daily at 2 AM):
 * 0 2 * * * /usr/bin/php /path/to/EASYBUY_Final/actions/expire_collaborations.php
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/collaboration_controller.php';

// Only allow this to run from command line or with a secret key
$secret_key = isset($argv[1]) ? $argv[1] : ($_GET['key'] ?? '');
$expected_key = 'expire_collabs_2025'; // Change this to a secure random string

if ($secret_key !== $expected_key && php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('Unauthorized');
}

$result = expire_old_collaborations_ctr();

if (php_sapi_name() === 'cli') {
    echo $result ? "Successfully expired old collaborations.\n" : "No collaborations to expire or error occurred.\n";
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $result ? 'success' : 'error',
        'message' => $result ? 'Collaborations expired successfully' : 'No collaborations to expire'
    ]);
}

