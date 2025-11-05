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
<title>Halloweeni jelmezverseny — Eredményhirdetés</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/custom.css">
<style>body{padding:20px}
.card {
  height: 400px;                  /* a bit taller for breathing room */
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  background-color: #2a2a2a;      /* matches your site background */
  border-radius: 10px;
  padding: 10px;
}

/* Image container consistency */
.card img.photo {
  width: 100%;                    /* don’t stretch beyond container */
  height: 180px;                  /* fixed visible height */
  object-fit: cover;              /* crop top/bottom neatly */
  border-radius: 10px;
  display: block;
}

/* Keep text tidy and aligned */
.card h5,
.card h4,
.card p {
  margin-bottom: 0.4rem;
  color: #ff8c00;                 /* match your orange accent */
  overflow: hidden;
  text-overflow: ellipsis;
}

</style>
</head>
<body>
<div class="container">
  <h1>Halloweeni jelmezverseny — Eredményhirdetés</h1>
  <?php if (!$is_admin): ?>
    <div class="alert alert-info">Az eredmények láthatóak, de csak a Host változtathat fázisokat</div>
  <?php endif; ?>
  <div class="row gy-3">
    <?php $i=0; foreach ($rows as $r): $i++; ?>
      <div class="col-12 col-md-4">
        <div class="card p-3 text-center">
          <?php if ($r['photo']): ?><img src="<?php echo h($r['photo']); ?>" class="photo img-thumbnail mb-2"><?php endif; ?>
          <h4><?php echo h($r['name']); ?></h4>
          <p><?php echo h($r['costume']); ?></p>
          <p style="font-size:1.2rem"><strong>Átlag: <?php echo $r['avg_score'] ? number_format($r['avg_score'],2) : '—'; ?></strong> (<?php echo $r['votes_count']; ?> szavazat)</p>
          <?php if ($i<=3): ?><div class="badge bg-success">Top #<?php echo $i; ?></div><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <hr>
  <!-- <p><?php if ($is_admin): ?><a href="start_vote.php">Host controls</a></p>
  <p><a href="logout.php">Logout</a><?php else: ?><a href="login.php">Host login</a><?php endif; ?></p>
  <p><a href="index.php">Back to register</a></p>
  <p><a href="vote.php">Vote</a></p> -->
<img src="assets/images/pumpkin.png" class="pumpkin" alt="pumpkin">
</div>

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

</body>
</html>
