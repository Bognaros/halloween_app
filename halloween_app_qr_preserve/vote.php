<?php
require 'inc/db.php';
require 'inc/functions.php';
session_start();
if (!isset($_SESSION['voter_uid'])) $_SESSION['voter_uid'] = bin2hex(random_bytes(8));
$voter_uid = $_SESSION['voter_uid'];
$phase = get_phase($pdo);
// handle votes POST
$errors = [];
$ok = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($phase !== 'voting') {
        $errors[] = 'Voting is not active.';
    } else {
        // check if this voter already voted
        $st = $pdo->prepare('SELECT COUNT(*) FROM votes WHERE voter_uid = :vuid');
        $st->execute([':vuid'=>$voter_uid]);
        $cnt = (int)$st->fetchColumn();
        if ($cnt > 0) {
            $errors[] = 'You have already voted.';
        } else {
            $scores = $_POST['score'] ?? [];
            $pdo->beginTransaction();
            $st = $pdo->prepare('INSERT INTO votes (participant_id,score,voter_uid) VALUES (:pid,:score,:vuid)');
            foreach ($scores as $pid => $s) {
                $s = (int)$s;
                if ($s < 1 || $s > 10) continue;
                $st->execute([':pid'=>$pid,':score'=>$s,':vuid'=>$voter_uid]);
            }
            $pdo->commit();
            $ok = true;
        }
    }
}
// fetch participants
$participants = $pdo->query('SELECT * FROM participants ORDER BY id')->fetchAll();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Vote — Halloween Contest</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>body{padding:20px}.photo{max-width:120px;}</style>
</head>
<body>
<div class="container">
  <h1>Vote — Halloween Contest</h1>
  <p>Phase: <strong><?php echo h($phase); ?></strong></p>
  <?php if ($phase !== 'voting'): ?>
    <div class="alert alert-warning">Voting is not active. Please wait for the host to start voting.</div>
  <?php endif; ?>
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><?php echo implode('<br>', array_map('h',$errors)); ?></div>
  <?php endif; ?>
  <?php if ($ok): ?>
    <div class="alert alert-success">Thanks for voting!</div>
  <?php endif; ?>

  <?php if ($participants): ?>
    <form method="post">
      <div class="row gy-3">
        <?php foreach ($participants as $p): ?>
          <div class="col-12 col-md-6">
            <div class="card p-2">
              <div class="d-flex gap-3">
                <?php if ($p['photo']): ?>
                  <img src="<?php echo h($p['photo']); ?>" class="photo img-thumbnail" alt="photo">
                <?php endif; ?>
                <div>
                  <h5><?php echo h($p['name']); ?> — <?php echo h($p['costume']); ?></h5>
                  <label>Score (1-10): <input type="number" min="1" max="10" name="score[<?php echo $p['id']; ?>]" value="5" required></label>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="mt-3">
        <?php if ($phase === 'voting' && !$ok): ?><button class="btn btn-primary">Submit my scores</button><?php endif; ?>
      </div>
    </form>
  <?php else: ?>
    <div class="alert alert-info">No participants yet.</div>
  <?php endif; ?>

  <hr>
  <p><a href="index.php">Back to register</a> | <a href="results.php">View results</a></p>
</div>
</body>
</html>
