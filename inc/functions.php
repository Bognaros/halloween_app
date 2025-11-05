<?php
require_once __DIR__ . '/db.php';

function h($s){ return htmlspecialchars($s, ENT_QUOTES); }

function save_uploaded_photo($file) {
    $allowed = ['image/jpeg','image/png','image/webp'];
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    if (!in_array($file['type'], $allowed)) return null;
    $uploadDir = __DIR__ . '/../assets/uploads';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $uploadDir . '/' . $name;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return 'assets/uploads/' . $name;
    }
    return null;
}

function participant_avg($pdo, $id) {
    $st = $pdo->prepare('SELECT AVG(score) as avg FROM votes WHERE participant_id=:id');
    $st->execute([':id'=>$id]);
    $r = $st->fetch();
    return $r['avg'] !== null ? round($r['avg'],2) : 0;
}
?>