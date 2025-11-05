<?php
$qrFile = __DIR__ . '/assets/images/qr.png';
if (!file_exists($qrFile)) die('QR code image not found.');
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Halloweeni jelmezverseny — — QR</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/custom.css">
<style>
/* Container like index.php with top padding */
.container.qr-container {
    padding-top: 20px;
}

/* Center QR code horizontally */
img.qr {
    display: block;
    margin: 60px auto 40px; /* centers and adds spacing above/below */
    width: 500px;
    max-width: 90%;
    height: auto;
    border: 5px solid #ff9900;
    border-radius: 10px;
}

/* Back link left-aligned */
.back-link {
    text-align: left;
    margin-top: 10px;
}

</style>
</head>
<body>
<div class="container qr-container mt-3">
    <h1>Scanneld be a kódot a csatlakozáshoz — QR</h1>
    <img src="assets/images/qr.png" alt="QR Code" class="qr">
    <p class="back-link"><a href="index.php">Vissza</a></p>
    <img src="assets/images/pumpkin.png" class="pumpkin" alt="pumpkin">
</div>
</body>

</html>