<?php
require 'inc/db.php';
session_start();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    // hardcoded credentials - change before event
    $ADMIN_USER = 'host';
    $ADMIN_PASS = 'admin123';
    if ($user === $ADMIN_USER && $pass === $ADMIN_PASS) {
        $_SESSION['is_admin'] = true;
        header('Location: start_vote.php'); exit;
    } else {
        $errors[] = 'Invalid credentials.';
    }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Host Login</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></head>
<body style="padding:20px"><div class="container"><h1>Host Login</h1>
<?php if ($errors): ?><div class="alert alert-danger"><?php echo implode('<br>', array_map('h',$errors)); ?></div><?php endif; ?>
<form method="post">
  <div class="mb-3"><label class="form-label">User</label><input name="user" class="form-control"></div>
  <div class="mb-3"><label class="form-label">Password</label><input type="password" name="pass" class="form-control"></div>
  <button class="btn btn-primary">Login</button>
</form>
<p><a href="index.php">Back</a></p>
</div></body></html>
