<?php
require 'inc/db.php';
require 'inc/functions.php';
$phase = get_phase($pdo);
// handle registration POST
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($phase !== 'registration') {
        $errors[] = 'A regisztráció le van zárva.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $costume = trim($_POST['costume'] ?? '');

        if (strlen($name) > 45) $errors[] = 'A Versenyző neve maximum 45 karakter lehet';
        if (strlen($costume) > 45) $errors[] = 'A Jelmez neve maximum 45 karakter lehet';

        if ($name === '' || $costume === '') $errors[] = 'Versenyző- és jelmeznév szükséges.';
        $photoPath = null;
        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $photoPath = save_uploaded_photo($_FILES['photo']);
            if (!$photoPath) $errors[] = 'Fénykép feltöltés sikertelen (jpg, png elfogadott)';
        }
        if (!$errors) {
            $st = $pdo->prepare('INSERT INTO participants (name,costume,photo) VALUES (:n,:c,:p)');
            $st->execute([':n'=>$name,':c'=>$costume,':p'=>$photoPath]);
            header('Location: index.php?ok=1');
            exit;
        }
    }
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Halloweeni jelmezverseny — Regisztráció</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/custom.css">
<style>body{padding:20px}</style>
</head>
<body>
<div class="container">
  <h1>Halloweeni jelmezverseny — Regisztráció</h1>
  <p>Fázis: <strong><?php echo h($phase); ?></strong></p>
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><?php echo implode('<br>', array_map('h',$errors)); ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Sikeres regisztráció</div>
  <?php endif; ?>

  <?php if ($phase === 'registration'): ?>
  <form method="post" enctype="multipart/form-data" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Versenyző neve</label>
      <input name="name" class="form-control" required maxlength="45">
    </div>
    <div class="col-md-6">
      <label class="form-label">Jelmez neve</label>
      <input name="costume" class="form-control" required maxlength="45">
    </div>
    <div class="col-12">
      <label class="form-label">Fénykép (opcionális) — jpg/png/webp, kliens oldalon formázva, ha túl nagy</label>
      <input type="file" name="photo" accept="image/*" class="form-control">
    </div>
    <div class="col-12">
      <button class="btn btn-primary">Regisztrál</button>
    </div>
  </form>
  <?php else: ?>
    <div class="alert alert-info">A regisztráció le van zárva. Kérd meg a host-ot, ha szükséges.</div>
  <?php endif; ?>

  <hr>
  <!-- <p>Already registered? <a href="vote.php">Go to voting</a></p> -->
  <p>Host vezérlés: <a href="login.php">Host belépés</a></p>
  <!--<p>Show results: <a href="results.php">Results</a></p> -->
  <p>Csatlakozás QR kóddal: <a href="qrcode.php">QR Kód</a></p>
  <img src="assets/images/pumpkin.png" class="pumpkin" alt="pumpkin">
</div>

<script>
document.querySelector('form')?.addEventListener('submit', async function(e){
  const fileInput = document.querySelector('input[name="photo"]');
  if (!fileInput || !fileInput.files.length) return;
  const file = fileInput.files[0];
  const img = new Image();
  const reader = new FileReader();
  const canvas = document.createElement('canvas');
  const ctx = canvas.getContext('2d');
  e.preventDefault();
  reader.onload = async function(ev){
    img.onload = function(){
      const maxDim = 800;
      let w = img.width;
      let h = img.height;
      if (w > h && w > maxDim) { h = h * (maxDim / w); w = maxDim; }
      else if (h > w && h > maxDim) { w = w * (maxDim / h); h = maxDim; }
      const targetWidth = 600;
      const scale = targetWidth / w;
      const scaledHeight = h * scale;

      // Draw the resized image to an offscreen canvas
      canvas.width = targetWidth;
      canvas.height = scaledHeight;
      ctx.drawImage(img, 0, 0, targetWidth, scaledHeight);

      // Now crop vertically to a fixed aspect ratio (square crop here)
      const cropSize = 600; // height to crop to (square)
      const yOffset = Math.max(0, (scaledHeight - cropSize) / 2); // center crop

      // Draw cropped result to a second canvas
      const cropped = document.createElement('canvas');
      cropped.width = targetWidth;
      cropped.height = cropSize;
      cropped.getContext('2d').drawImage(canvas, 0, yOffset, targetWidth, cropSize, 0, 0, targetWidth, cropSize);
      cropped.toBlob(function(blob){
        const newFile = new File([blob], 'compressed.jpg', {type:'image/jpeg'});
        const dt = new DataTransfer();
        dt.items.add(newFile);
        fileInput.files = dt.files;
        e.target.submit();
      }, 'image/jpeg', 0.8);
    };
    img.src = ev.target.result;
  };
  reader.readAsDataURL(file);
});
</script>

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

