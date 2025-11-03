<?php
require 'inc/db.php';
require 'inc/functions.php';
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php'); exit;
}
$msg = null;
if (isset($_GET['action'])) {
    $a = $_GET['action'];
    if ($a === 'start') { set_phase($pdo,'voting'); $msg = 'Voting started'; }
    if ($a === 'stop') { set_phase($pdo,'results'); $msg = 'Voting stopped, showing results'; }
    if ($a === 'open') { set_phase($pdo,'registration'); $msg = 'Registration open'; }
}
$phase = get_phase($pdo);
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Host Controls</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head><body><div class="container" style="padding:20px">
  <h1>Host Controls</h1>
  <p>Phase: <strong><?php echo h($phase); ?></strong></p>
  <?php if ($msg): ?><div class="alert alert-success"><?php echo h($msg); ?></div><?php endif; ?>
  <p>Actions: <code>start</code> (begin voting), <code>stop</code> (end voting & show results), <code>open</code> (open registration)</p>
  <p>
    <a class="btn btn-primary" href="start_vote.php?action=open">Open registration</a>
    <a class="btn btn-success" href="start_vote.php?action=start">Start voting</a>
    <a class="btn btn-danger" href="start_vote.php?action=stop">Stop voting (show results)</a>
  </p>
  <hr>
  <p><a href="results.php">Back to results</a> | <a href="logout.php">Logout</a></p>
</div></body></html>
