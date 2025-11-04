<?php
require 'inc/db.php';
require 'inc/functions.php';
session_start();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    // hardcoded credentials - change before event
    $ADMIN_USER = 'host';
    $ADMIN_PASS = 'supersecret';
    if ($user === $ADMIN_USER && $pass === $ADMIN_PASS) {
        $_SESSION['is_admin'] = true;
        header('Location: start_vote.php'); exit;
    } else {
        $errors[] = 'Érvénytelen belépési adatok.';
    }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Host Belépés</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/custom.css"></head>
<body style="padding:20px">
<div class="container"><h1>Host Belépés</h1>
<?php if ($errors): ?><div class="alert alert-danger"><?php echo implode('<br>', array_map('h',$errors)); ?></div><?php endif; ?>
<form method="post">
  <div class="mb-3"><label class="form-label">Felhasználó</label><input name="user" class="form-control"></div>
  <div class="mb-3"><label class="form-label">Jelszó</label><input type="password" name="pass" class="form-control"></div>
  <button class="btn btn-primary">Belépés</button>
</form>
<p><a href="index.php">Vissza</a></p>
<img src="assets/images/pumpkin.png" class="pumpkin" alt="pumpkin">
</div>

</body></html>
