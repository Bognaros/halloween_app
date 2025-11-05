<?php 
require 'inc/db.php';
require 'inc/functions.php';
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php'); 
    exit;
}

$rows = $pdo->query('
  SELECT 
    p.*, 
    (SELECT SUM(score) FROM votes v WHERE v.participant_id = p.id) AS total_score,
    (SELECT AVG(score) FROM votes v WHERE v.participant_id = p.id) AS avg_score,
    (SELECT COUNT(*) FROM votes v WHERE v.participant_id = p.id) AS votes_count
  FROM participants p
  ORDER BY total_score DESC NULLS LAST, avg_score DESC
')->fetchAll();


// Phase actions
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
<link rel="stylesheet" href="assets/css/custom.css">

<script>
  setInterval(function() {
    location.reload();
  }, 10000); // reload every 1000 ms (1 second)
</script>

</head><body>
<div class="container" style="padding:20px">
  <h1>Host Controls</h1>
  <p>Phase: <strong><?php echo h($phase); ?></strong></p>

  <?php if ($msg): ?>
    <div class="alert alert-success"><?php echo h($msg); ?></div>
  <?php endif; ?>

  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'reset_done'): ?>
    <div class="alert alert-success">Application reset successfully.</div>
  <?php endif; ?>

  <p>Actions: <code>start</code> (begin voting), <code>stop</code> (end voting & show results), <code>open</code> (open registration)</p>
  <p>
    <a class="btn btn-warning" href="start_vote.php?action=open">Open registration</a>
    <a class="btn btn-success" href="start_vote.php?action=start">Start voting</a>
    <a class="btn btn-primary" href="start_vote.php?action=stop">Stop voting (show results)</a>
    <a class="btn btn-danger" href="reset.php" onclick="return confirm('Are you sure? This will delete the database and all uploads!')">Reset Application</a>
  </p>
  <hr>

  <?php $i=0; foreach ($rows as $r): $i++; ?>
    <?php
      // assign rank colors
      $rankClass = '';
      if ($i === 1) $rankClass = 'gold';
      elseif ($i === 2) $rankClass = 'silver';
      elseif ($i === 3) $rankClass = 'bronze';
    ?>
    <div class="col-12">
      <div class="card result-card <?php echo $rankClass; ?> p-3 text-center">
        <?php if ($r['photo']): ?>
          <img src="<?php echo h($r['photo']); ?>" class="photo" alt="photo">
        <?php endif; ?>
        <div class="result-card-content">
          <h4><?php echo h($r['name']); ?></h4>
          <p><?php echo h($r['costume']); ?></p>
          <p style="font-size:1.2rem">
            <strong>Összpont: <?php echo $r['total_score'] ? number_format($r['total_score'],0) : '—'; ?></strong><br>
            Átlag: <?php echo $r['avg_score'] ? number_format($r['avg_score'],2) : '—'; ?> 
            (<?php echo $r['votes_count']; ?> szavazat)
          </p>
          <?php if ($i<=3): ?><div class="badge rank-badge">Top #<?php echo $i; ?></div><?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>


  <!-- <p><a href="results.php">Back to results</a> | <a href="logout.php">Logout</a></p> -->
<a href="index.php">
  <img src="assets/images/pumpkin.png" class="pumpkin" alt="pumpkin">
</a>
</div>
</body></html>