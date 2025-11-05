<?php
session_start();

// Clear session
session_unset();
session_destroy();

// Delete SQLite database
$dbFile = __DIR__ . '/db/halloween.db';
if (file_exists($dbFile)) unlink($dbFile);

// Delete uploaded images
$uploadDir = __DIR__ . '/assets/uploads/';
foreach (glob($uploadDir . '*') as $file) {
    if (is_file($file)) unlink($file);
}

// Redirect back to host panel
header('Location: start_vote.php?msg=reset_done');
exit;
