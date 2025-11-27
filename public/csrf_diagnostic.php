<?php
/**
 * CSRF Diagnostic Tool
 * Access via: http://localhost/sunuframework2/csrf_diagnostic.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Core\Security\CSRF;

$csrf = CSRF::getInstance();

// Handle test form submission
$testResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_submit'])) {
    $submittedToken = $_POST['_csrf_token'] ?? null;
    $sessionToken = $_SESSION[CSRF::getTokenName()] ?? null;
    $isValid = $csrf->validateToken($submittedToken);

    $testResult = [
        'submitted_token' => $submittedToken,
        'session_token' => $sessionToken,
        'is_valid' => $isValid,
        'tokens_match' => $submittedToken === $sessionToken
    ];
}

// Get current token
$currentToken = $csrf->getToken();

?>
<!DOCTYPE html>
<html>
<head>
    <title>CSRF Diagnostic Tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
        }
        .section {
            background: #f5f5f5;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        table td, table th {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table th {
            background: #007bff;
            color: white;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .token {
            word-break: break-all;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <h1>🔐 CSRF Diagnostic Tool</h1>

    <?php if ($testResult): ?>
        <div class="section <?= $testResult['is_valid'] ? 'success' : 'error' ?>">
            <h2>Test Result: <?= $testResult['is_valid'] ? '✅ SUCCESS' : '❌ FAILED' ?></h2>
            <table>
                <tr>
                    <th>Property</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td><strong>Submitted Token</strong></td>
                    <td class="token"><?= htmlspecialchars($testResult['submitted_token'] ?? 'NULL') ?></td>
                </tr>
                <tr>
                    <td><strong>Session Token</strong></td>
                    <td class="token"><?= htmlspecialchars($testResult['session_token'] ?? 'NULL') ?></td>
                </tr>
                <tr>
                    <td><strong>Tokens Match</strong></td>
                    <td><?= $testResult['tokens_match'] ? '✅ YES' : '❌ NO' ?></td>
                </tr>
                <tr>
                    <td><strong>Validation Result</strong></td>
                    <td><?= $testResult['is_valid'] ? '✅ VALID' : '❌ INVALID' ?></td>
                </tr>
            </table>

            <?php if (!$testResult['is_valid']): ?>
                <p><strong>⚠️ Diagnosis:</strong></p>
                <ul>
                    <?php if (!$testResult['submitted_token']): ?>
                        <li>Token was not submitted in the POST request</li>
                    <?php endif; ?>
                    <?php if (!$testResult['session_token']): ?>
                        <li>No token found in session</li>
                    <?php endif; ?>
                    <?php if ($testResult['submitted_token'] && $testResult['session_token'] && !$testResult['tokens_match']): ?>
                        <li>Submitted token doesn't match session token</li>
                        <li>This could indicate: session regeneration, multiple tabs, or race conditions</li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="section">
        <h2>Session Information</h2>
        <table>
            <tr>
                <td><strong>Session ID</strong></td>
                <td class="token"><?= session_id() ?></td>
            </tr>
            <tr>
                <td><strong>Session Status</strong></td>
                <td><?= session_status() === PHP_SESSION_ACTIVE ? '✅ Active' : '❌ Inactive' ?></td>
            </tr>
            <tr>
                <td><strong>Current CSRF Token</strong></td>
                <td class="token"><?= htmlspecialchars($currentToken) ?></td>
            </tr>
            <tr>
                <td><strong>Token in Session</strong></td>
                <td class="token"><?= htmlspecialchars($_SESSION[CSRF::getTokenName()] ?? 'NULL') ?></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Session Configuration</h2>
        <table>
            <tr>
                <td><strong>session.use_cookies</strong></td>
                <td><?= ini_get('session.use_cookies') ? '✅ Enabled' : '❌ Disabled' ?></td>
            </tr>
            <tr>
                <td><strong>session.cookie_httponly</strong></td>
                <td><?= ini_get('session.cookie_httponly') ? '✅ Enabled' : '❌ Disabled' ?></td>
            </tr>
            <tr>
                <td><strong>session.cookie_lifetime</strong></td>
                <td><?= ini_get('session.cookie_lifetime') ?> seconds</td>
            </tr>
            <tr>
                <td><strong>session.cookie_path</strong></td>
                <td><?= ini_get('session.cookie_path') ?></td>
            </tr>
            <tr>
                <td><strong>session.cookie_domain</strong></td>
                <td><?= ini_get('session.cookie_domain') ?: '(empty - uses current domain)' ?></td>
            </tr>
            <tr>
                <td><strong>session.cookie_samesite</strong></td>
                <td><?= ini_get('session.cookie_samesite') ?: 'Not set' ?></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Test CSRF Validation</h2>
        <p>This form will submit to itself with the current CSRF token. If validation works, you'll see a success message above.</p>

        <form method="POST" action="">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($currentToken) ?>">
            <input type="hidden" name="test_submit" value="1">
            <button type="submit">🧪 Test CSRF Validation</button>
        </form>

        <p style="margin-top: 20px;"><small>Note: This test form includes the token value shown above.</small></p>
    </div>

    <div class="section warning">
        <h2>⚠️ Common Issues</h2>
        <ul>
            <li><strong>Session not persisting:</strong> Check if cookies are enabled in browser</li>
            <li><strong>Token mismatch:</strong> Multiple tabs can cause token regeneration</li>
            <li><strong>Cookie domain issues:</strong> Ensure cookie domain matches your URL</li>
            <li><strong>HTTPS vs HTTP:</strong> Mixing protocols can cause cookie issues</li>
        </ul>
    </div>

    <div class="section">
        <h2>Log Files</h2>
        <ul>
            <li><strong>CSRF Debug Log:</strong> <code>storage/logs/csrf_debug.log</code></li>
            <li><strong>CSRF Field Log:</strong> <code>storage/logs/debug_csrf.log</code></li>
        </ul>

        <?php
        $csrfDebugLog = __DIR__ . '/../storage/logs/csrf_debug.log';
        if (file_exists($csrfDebugLog)):
            $logLines = file($csrfDebugLog);
            $lastLines = array_slice($logLines, -10);
        ?>
            <h3>Last 10 entries from csrf_debug.log:</h3>
            <pre style="background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto;"><?= htmlspecialchars(implode('', $lastLines)) ?></pre>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>Next Steps</h2>
        <ol>
            <li>Click the "Test CSRF Validation" button above</li>
            <li>If it succeeds, the problem is specific to the language management forms</li>
            <li>If it fails, check session configuration and browser cookies</li>
            <li>Check the log files for more details</li>
        </ol>
    </div>
</body>
</html>
