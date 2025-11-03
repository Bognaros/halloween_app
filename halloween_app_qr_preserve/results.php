<?php
require 'inc/db.php';
require 'inc/functions.php';
session_start();
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
// compute averages and order
$rows = $pdo->query('SELECT p.*, (SELECT AVG(score) FROM votes v WHERE v.participant_id = p.id) as avg_score, (SELECT COUNT(*) FROM votes v WHERE v.participant_id = p.id) as votes_count FROM participants p ORDER BY avg_score DESC NULLS LAST')->fetchAll();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Results — Halloween Contest</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>body{padding:20px}.photo{max-width:160px}</style>
</head>
<body>
<div class="container">
  <h1>Results — Halloween Contest</h1>
  <?php if (!$is_admin): ?>
    <div class="alert alert-info">Results are visible, but only the host can change phases.</div>
  <?php endif; ?>
  <div class="row gy-3">
    <?php $i=0; foreach ($rows as $r): $i++; ?>
      <div class="col-12 col-md-4">
        <div class="card p-3 text-center">
          <?php if ($r['photo']): ?><img src="<?php echo h($r['photo']); ?>" class="photo img-thumbnail mb-2"><?php endif; ?>
          <h4><?php echo h($r['name']); ?></h4>
          <p><?php echo h($r['costume']); ?></p>
          <p style="font-size:1.2rem"><strong>Avg: <?php echo $r['avg_score'] ? number_format($r['avg_score'],2) : '—'; ?></strong> (<?php echo $r['votes_count']; ?> votes)</p>
          <?php if ($i<=3): ?><div class="badge bg-success">Top #<?php echo $i; ?></div><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <hr>
  <p><?php if ($is_admin): ?><a href="start_vote.php">Host controls</a> | <a href="logout.php">Logout</a><?php else: ?><a href="login.php">Host login</a><?php endif; ?></p>
  <p><a href="index.php">Back to register</a> | <a href="vote.php">Vote</a></p>
</div>
</body>
</html>
