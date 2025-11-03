<?php
require_once __DIR__ . '/inc/qrlib.php';
$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$host = $_SERVER['HTTP_HOST'];
$url = $scheme . '://' . $host . $base . '/index.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html><html><head><meta charset="utf-8"><title>QR Join</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></head>
<body style="padding:20px"><div class="container">
  <h1>Scan to join</h1>
  <p>Open this URL on your phone to register:</p>
  <p><code><?php echo htmlspecialchars($url); ?></code></p>
  <div class="text-center border p-3">
  <?php
    ob_start();
    QRcode::png($url, false, QR_ECLEVEL_L, 6);
    $imageData = ob_get_contents();
    ob_end_clean();
    echo '<img src="data:image/png;base64,' . base64_encode($imageData) . '" alt="QR">';
  ?>
  </div>
  <p class="mt-3">Or share the link above.</p>
  <p><a href="index.php">Back</a></p>
</div></body></html>
