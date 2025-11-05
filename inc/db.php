<?php
// Simple SQLite connection (PDO)
$dbFile = __DIR__ . '/../db/halloween.db';
if (!file_exists(dirname($dbFile))) mkdir(dirname($dbFile), 0755, true);
$dsn = 'sqlite:' . $dbFile;
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
$pdo = new PDO($dsn, null, null, $options);
$pdo->exec('PRAGMA journal_mode = WAL;');

$pdo->exec("CREATE TABLE IF NOT EXISTS participants (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    costume TEXT NOT NULL,
    photo TEXT,
    created_at INTEGER NOT NULL DEFAULT (strftime('%s','now'))
);"); 

$pdo->exec("CREATE TABLE IF NOT EXISTS votes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    participant_id INTEGER NOT NULL,
    score INTEGER NOT NULL,
    voter_uid TEXT,
    created_at INTEGER NOT NULL DEFAULT (strftime('%s','now')),
    FOREIGN KEY(participant_id) REFERENCES participants(id)
);"); 

$pdo->exec("CREATE TABLE IF NOT EXISTS meta (
    k TEXT PRIMARY KEY,
    v TEXT
);"); 

$stmt = $pdo->prepare("INSERT OR IGNORE INTO meta (k,v) VALUES ('phase','registration')");
$stmt->execute();

function get_phase($pdo) {
    $st = $pdo->query("SELECT v FROM meta WHERE k='phase'");
    $r = $st->fetchColumn();
    return $r ?: 'registration';
}

function set_phase($pdo, $phase) {
    $st = $pdo->prepare("INSERT OR REPLACE INTO meta (k,v) VALUES ('phase', :p)");
    $st->execute([':p'=>$phase]);
}
?>