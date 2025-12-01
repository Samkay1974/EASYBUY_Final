<?php
/**
 * Test script to check callback URL configuration
 * This file should be accessible at: yourdomain.com/EASYBUY_Final/test_callback_url.php
 * DELETE THIS FILE AFTER TESTING
 */

// Include Paystack config to test URL detection
require_once __DIR__ . '/settings/paystack_config.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Callback URL Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .info { background: #e3f2fd; padding: 15px; border-left: 4px solid #2196F3; margin: 10px 0; border-radius: 4px; }
        .success { background: #e8f5e9; padding: 15px; border-left: 4px solid #4CAF50; margin: 10px 0; border-radius: 4px; }
        .error { background: #ffebee; padding: 15px; border-left: 4px solid #f44336; margin: 10px 0; border-radius: 4px; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        .url-test { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 10px 0; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Paystack Callback URL Test</h1>
        
        <div class="info">
            <strong>⚠️ IMPORTANT:</strong> Delete this file (<code>test_callback_url.php</code>) after testing for security.
        </div>

        <h2>Server Information</h2>
        <table>
            <tr>
                <th>Variable</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>HTTP_HOST</td>
                <td><code><?php echo isset($_SERVER['HTTP_HOST']) ? htmlspecialchars($_SERVER['HTTP_HOST']) : 'NOT SET'; ?></code></td>
            </tr>
            <tr>
                <td>SERVER_NAME</td>
                <td><code><?php echo isset($_SERVER['SERVER_NAME']) ? htmlspecialchars($_SERVER['SERVER_NAME']) : 'NOT SET'; ?></code></td>
            </tr>
            <tr>
                <td>DOCUMENT_ROOT</td>
                <td><code><?php echo isset($_SERVER['DOCUMENT_ROOT']) ? htmlspecialchars($_SERVER['DOCUMENT_ROOT']) : 'NOT SET'; ?></code></td>
            </tr>
            <tr>
                <td>SCRIPT_NAME</td>
                <td><code><?php echo isset($_SERVER['SCRIPT_NAME']) ? htmlspecialchars($_SERVER['SCRIPT_NAME']) : 'NOT SET'; ?></code></td>
            </tr>
            <tr>
                <td>REQUEST_URI</td>
                <td><code><?php echo isset($_SERVER['REQUEST_URI']) ? htmlspecialchars($_SERVER['REQUEST_URI']) : 'NOT SET'; ?></code></td>
            </tr>
            <tr>
                <td>HTTPS</td>
                <td><code><?php echo isset($_SERVER['HTTPS']) ? htmlspecialchars($_SERVER['HTTPS']) : 'NOT SET'; ?></code></td>
            </tr>
            <tr>
                <td>SERVER_PORT</td>
                <td><code><?php echo isset($_SERVER['SERVER_PORT']) ? htmlspecialchars($_SERVER['SERVER_PORT']) : 'NOT SET'; ?></code></td>
            </tr>
        </table>

        <h2>Detected Configuration</h2>
        
        <?php if (defined('APP_BASE_URL')): ?>
            <div class="success">
                <strong>✓ APP_BASE_URL:</strong><br>
                <code><?php echo htmlspecialchars(APP_BASE_URL); ?></code>
            </div>
        <?php else: ?>
            <div class="error">
                <strong>✗ APP_BASE_URL:</strong> Not defined
            </div>
        <?php endif; ?>

        <?php if (defined('PAYSTACK_CALLBACK_URL')): ?>
            <div class="success">
                <strong>✓ PAYSTACK_CALLBACK_URL:</strong><br>
                <code><?php echo htmlspecialchars(PAYSTACK_CALLBACK_URL); ?></code>
            </div>
        <?php else: ?>
            <div class="error">
                <strong>✗ PAYSTACK_CALLBACK_URL:</strong> Not defined
            </div>
        <?php endif; ?>

        <h2>Callback URL Test</h2>
        <?php if (defined('PAYSTACK_CALLBACK_URL')): ?>
            <div class="url-test">
                <strong>Test the callback URL:</strong><br>
                <a href="<?php echo htmlspecialchars(PAYSTACK_CALLBACK_URL); ?>" target="_blank">
                    <?php echo htmlspecialchars(PAYSTACK_CALLBACK_URL); ?>
                </a>
                <p><small>Click the link above to test if the callback page is accessible. You should see the Paystack callback page (you may be redirected to login if not logged in).</small></p>
            </div>
        <?php endif; ?>

        <h2>Expected File Location</h2>
        <div class="info">
            <strong>Callback file should be at:</strong><br>
            <code><?php echo htmlspecialchars(__DIR__ . '/view/paystack_callback.php'); ?></code><br><br>
            <strong>File exists:</strong> 
            <?php 
            $callbackFile = __DIR__ . '/view/paystack_callback.php';
            if (file_exists($callbackFile)) {
                echo '<span style="color: green;">✓ YES</span>';
            } else {
                echo '<span style="color: red;">✗ NO - FILE MISSING!</span>';
            }
            ?>
        </div>

        <h2>Manual Configuration</h2>
        <div class="info">
            <p>If the auto-detected URLs are incorrect, you can manually set them in <code>settings/server_cred.php</code> by adding:</p>
            <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto;">
if (!defined('APP_BASE_URL')) {
    define('APP_BASE_URL', 'https://yourdomain.com/EASYBUY_Final');
}
</pre>
        </div>
    </div>
</body>
</html>

