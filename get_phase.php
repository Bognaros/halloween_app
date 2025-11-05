<?php
// get_phase.php
// Returns the current app phase as JSON: { "phase": "registration"|"voting"|"results" }

require 'inc/db.php';      // must expose $pdo and get_phase($pdo)
require 'inc/functions.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0'); // avoid caching

echo json_encode(['phase' => get_phase($pdo)]);