<?php
// Set error reporting settings
ini_set('display_errors', 0);
error_reporting(E_ALL);

/**
 * Custom error handler function
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // Log the error
    error_log("Error [$errno] $errstr in $errfile:$errline");

    // Handle based on error type
    if ($errno == E_USER_ERROR) {
        redirect_to_error(500);
    }

    // Don't execute PHP's internal error handler
    return true;
}

/**
 * Custom exception handler function
 */
function customExceptionHandler($exception) {
    // Log the exception
    error_log("Exception: " . $exception->getMessage() . " in " .
        $exception->getFile() . ":" . $exception->getLine());

    redirect_to_error(500);
}

/**
 * Handle 404 errors (page not found)
 */
function handle_404() {
    redirect_to_error(404);
}

/**
 * Handle 403 errors (forbidden)
 */
function handle_403($redirect_to_login = false) {
    if ($redirect_to_login) {
        // For unauthorized users, we'll redirect to login instead
        header("Location: /login.php");
        exit;
    } else {
        // For forbidden access (authenticated but not authorized)
        redirect_to_error(403);
    }
}

/**
 * Redirect to appropriate error page
 */
function redirect_to_error($code) {
    http_response_code($code);

    // If AJAX request - return JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {

        header('Content-Type: application/json');

        $messages = [
            403 => 'Access forbidden',
            404 => 'Resource not found',
            500 => 'Internal server error'
        ];

        echo json_encode(["error" => $messages[$code] ?? 'Unknown error']);
        exit;
    }

    // For regular requests, redirect to error page using relative path
    header("Location: /dmuk-coursework/errors/{$code}.php");
    exit;
}

// Set custom error handlers
set_error_handler("customErrorHandler");
set_exception_handler("customExceptionHandler");