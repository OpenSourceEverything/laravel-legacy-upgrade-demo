<?php

// Compare actual rows, IDs, hashes, quantities and timestamps from the same
// persistent SQLite file. PHPUnit's in-memory resets cannot prove continuity.
require __DIR__.'/../bootstrap/autoload.php';
require_once __DIR__.'/SnapshotRows.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = $app->make('db');
$snapshot = [];
foreach (['users', 'categories', 'items', 'stock_movements'] as $table) {
    $snapshot[$table] = [];
    foreach ($db->table($table)->orderBy('id')->get() as $row) {
        $snapshot[$table][] = Demo\Verification\SnapshotRows::normalize($table, (array) $row);
    }
}
if (count($snapshot['items']) === 0 || count($snapshot['stock_movements']) === 0) {
    fwrite(STDERR, "A nonempty seeded inventory is required.\n"); exit(1);
}
if (count($db->select('PRAGMA foreign_key_check')) !== 0 || $db->select('PRAGMA integrity_check')[0]->integrity_check !== 'ok') {
    fwrite(STDERR, "SQLite integrity check failed.\n"); exit(1);
}
foreach ($db->table('items')->get() as $item) {
    $sum = $db->table('stock_movements')->where('item_id', $item->id)->sum('delta');
    if ((int) $sum !== (int) $item->quantity) {
        fwrite(STDERR, "Stock balance differs from its ledger.\n"); exit(1);
    }
}
$file = __DIR__.'/../storage/baseline-data.json';
$json = json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (in_array('--record', $argv, true)) {
    if (file_exists($file)) { fwrite(STDERR, "Baseline already exists; refusing to replace it.\n"); exit(1); }
    file_put_contents($file, $json);
} elseif (!file_exists($file) || json_decode(file_get_contents($file), true) !== $snapshot) {
    fwrite(STDERR, "Stored rows differ from the baseline.\n"); exit(1);
}
echo 'Data continuity OK; sha256='.hash('sha256', $json).PHP_EOL;
