<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../utils/error_handler.php";

// Unauthorized access log
const SECURITY_LOG = __DIR__ . "/../logs/security.log";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    logUnauthorizedAccess();
    handle_403();

    // If AJAX request — return JSON with error
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Unauthorized"]);
        exit;
    }

    // If not AJAX — redirect to login page
    header("Location: /dmuk-coursework/login.php");
    exit;
}

// Protect against session hijacking
if (!isset($_SESSION['fingerprint'])) {
    $_SESSION['fingerprint'] = hash("sha256", $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
} elseif ($_SESSION['fingerprint'] !== hash("sha256", $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'])) {
    session_destroy();
    logUnauthorizedAccess("Session hijacking detected!");
    header("Location: login.php");
    exit;
}

// Function to log unauthorized access
function logUnauthorizedAccess($message = "Unauthorized access attempt.") {
    $logData = sprintf(
        "[%s] %s | IP: %s | User-Agent: %s\n",
        date("Y-m-d H:i:s"),
        $message,
        $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
    );
    file_put_contents(SECURITY_LOG, $logData, FILE_APPEND);
}

