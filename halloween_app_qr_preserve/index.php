<?php
require 'inc/db.php';
require 'inc/functions.php';
$phase = get_phase($pdo);
// handle registration POST
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($phase !== 'registration') {
        $errors[] = 'Registration is closed.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $costume = trim($_POST['costume'] ?? '');
        if ($name === '' || $costume === '') $errors[] = 'Name and costume required.';
        $photoPath = null;
        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $photoPath = save_uploaded_photo($_FILES['photo']);
            if (!$photoPath) $errors[] = 'Photo upload failed (allowed: jpg, png, webp).';
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
<title>Halloween Costume Contest — Register</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>body{padding:20px}</style>
</head>
<body>
<div class="container">
  <h1>Halloween Costume Contest — Register</h1>
  <p>Phase: <strong><?php echo h($phase); ?></strong></p>
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><?php echo implode('<br>', array_map('h',$errors)); ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Registered! Thank you.</div>
  <?php endif; ?>

  <?php if ($phase === 'registration'): ?>
  <form method="post" enctype="multipart/form-data" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Your name</label>
      <input name="name" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Costume name</label>
      <input name="costume" class="form-control" required>
    </div>
    <div class="col-12">
      <label class="form-label">Photo (optional) — jpg/png/webp, will be resized client-side if large</label>
      <input type="file" name="photo" accept="image/*" class="form-control">
    </div>
    <div class="col-12">
      <button class="btn btn-primary">Register</button>
    </div>
  </form>
  <?php else: ?>
    <div class="alert alert-info">Registration is closed. Ask the host to re-open if needed.</div>
  <?php endif; ?>

  <hr>
  <p>Already registered? <a href="vote.php">Go to voting</a></p>
  <p>Host controls: <a href="login.php">Host login</a></p>
  <p>Show results: <a href="results.php">Results</a></p>
  <p>Join via QR: <a href="qrcode.php" target="_blank">QR Code</a></p>
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
      canvas.width = w; canvas.height = h;
      ctx.drawImage(img, 0, 0, w, h);
      canvas.toBlob(function(blob){
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
</body>
</html>

