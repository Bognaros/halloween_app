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
        $errors[] = 'A szavazás nem aktív';
    } else {
        // check if this voter already voted
        $st = $pdo->prepare('SELECT COUNT(*) FROM votes WHERE voter_uid = :vuid');
        $st->execute([':vuid'=>$voter_uid]);
        $cnt = (int)$st->fetchColumn();
        if ($cnt > 0) {
            $errors[] = 'Már adtál le szavazatot';
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
<title>Halloweeni jelmezverseny — Szavazás</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/custom.css">

</head>
<body>
<div class="container">
  <h1>Halloweeni jelmezverseny — Szavazás</h1>
  <p>Fázis: <strong><?php echo h($phase); ?></strong></p>
  <?php if ($phase !== 'voting'): ?>
    <div class="alert alert-warning">A szavazás nem aktív, várj a kezdésig.</div>
  <?php endif; ?>
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><?php echo implode('<br>', array_map('h',$errors)); ?></div>
  <?php endif; ?>
  <?php if ($ok): ?>
    <div class="alert alert-success">Köszi a szavazatot!</div>
  <?php endif; ?>

<?php if ($participants): ?>
<form method="post">
    <div class="d-flex flex-column gap-3">
        <?php foreach ($participants as $p): ?>
        <div class="card p-3">
            <?php if ($p['photo']): ?>
            <img src="<?php echo h($p['photo']); ?>" class="photo img-thumbnail mb-2" alt="photo">
            <?php endif; ?>
            <h5><?php echo h($p['name']); ?> — <?php echo h($p['costume']); ?></h5>
            <div class="mt-2">
                <label>Érték (1-10):</label>
                <input type="number" min="1" max="10" name="score[<?php echo $p['id']; ?>]" value="5" class="form-control form-control-lg" required>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="mt-3">
        <?php if ($phase === 'voting' && !$ok): ?>
        <button class="btn btn-primary btn-lg">Szavazat leadása</button>
        <?php endif; ?>
    </div>
</form>
<?php else: ?>
<div class="alert alert-info">Még nincsenek résztvevők.</div>
<?php endif; ?>


  <hr>
  <!-- <p><a href="index.php">Back to register</a></p>
  <p><a href="results.php">View results</a></p> -->
<a href="index.php">
  <img src="assets/images/pumpkin.png" class="pumpkin" alt="pumpkin">
</a>
</div>
</body>

<script>
(function() {
  // Poll interval in milliseconds
  const POLL_MS = 3000;

  // Helper to get the page name (vote.php, results.php, index.php...)
  function currentPage() {
    return window.location.pathname.split('/').pop().toLowerCase();
  }

  // Map server phase -> page to redirect to
  const phaseToPage = {
    'voting': 'vote.php',
    'results': 'results.php',
    'registration': 'index.php'
  };

  // Poll function
  async function checkPhaseAndRedirect() {
    try {
      const r = await fetch('get_phase.php', { cache: 'no-store' });
      if (!r.ok) return;
      const data = await r.json();
      const phase = (data.phase || '').toLowerCase();

      // If this phase maps to a page and we're not already there, redirect.
      if (phaseToPage[phase]) {
        const target = phaseToPage[phase];
        const curr = currentPage();
        if (curr !== target && curr !== '') {
          // Avoid redirecting host control pages or assets by ensuring target exists
          window.location.href = target;
        }
      }
    } catch (err) {
      // network errors are OK (silently ignore until next poll)
      // console.error('Phase check failed', err);
    }
  }

  // Start polling
  setInterval(checkPhaseAndRedirect, POLL_MS);

  // Optional: check immediately on load
  checkPhaseAndRedirect();
})();
</script>
</html>
