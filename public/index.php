<?php

// Require the class loader to enable automatic loading of classes
require __DIR__ . '/../Framework/ClassLoader.php';

// Start session globally in front controller
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// NOTE: CSRF helpers removed - no automatic token generation on every request

use Framework\Core\App;

try {
    // Create an instance of the App class
    $app = new App();

    // Run the application
    $app->run();
} catch (Exception $e) {
    // Handle any exceptions that occur during the application run
    die('An error occurred: ' . $e->getMessage());
}
