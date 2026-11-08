<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=school_lms", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== USERS TABLE ===\n";
$cols = $pdo->query("SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='school_lms' AND TABLE_NAME='users' ORDER BY ORDINAL_POSITION")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "  " . $c['COLUMN_NAME'] . " " . $c['DATA_TYPE'] . ($c['CHARACTER_MAXIMUM_LENGTH'] ? "(" . $c['CHARACTER_MAXIMUM_LENGTH'] . ")" : "") . " " . ($c['IS_NULLABLE'] === 'YES' ? 'NULL' : 'NOT NULL') . " default=" . var_export($c['COLUMN_DEFAULT'], true) . "\n";
}

echo "\n=== USERS INDEXES ===\n";
$idxs = $pdo->query("SELECT INDEX_NAME, COLUMN_NAME, NON_UNIQUE FROM information_schema.STATISTICS WHERE TABLE_SCHEMA='school_lms' AND TABLE_NAME='users' ORDER BY INDEX_NAME, SEQ_IN_INDEX")->fetchAll(PDO::FETCH_ASSOC);
foreach ($idxs as $i) {
    echo "  " . $i['INDEX_NAME'] . " on " . $i['COLUMN_NAME'] . " " . (!$i['NON_UNIQUE'] ? 'UNIQUE' : 'INDEX') . "\n";
}

echo "\n=== USERS FOREIGN KEYS ===\n";
$fks = $pdo->query("SELECT kcu.CONSTRAINT_NAME, kcu.COLUMN_NAME, kcu.REFERENCED_TABLE_NAME, kcu.REFERENCED_COLUMN_NAME, rc.DELETE_RULE FROM information_schema.KEY_COLUMN_USAGE kcu LEFT JOIN information_schema.REFERENTIAL_CONSTRAINTS rc ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME AND kcu.TABLE_SCHEMA = rc.CONSTRAINT_SCHEMA WHERE kcu.TABLE_SCHEMA='school_lms' AND kcu.TABLE_NAME='users' AND kcu.REFERENCED_TABLE_NAME IS NOT NULL ORDER BY kcu.CONSTRAINT_NAME, kcu.COLUMN_NAME")->fetchAll(PDO::FETCH_ASSOC);
foreach ($fks as $fk) {
    echo "  " . $fk['CONSTRAINT_NAME'] . ": " . $fk['COLUMN_NAME'] . " -> " . $fk['REFERENCED_TABLE_NAME'] . "(" . $fk['REFERENCED_COLUMN_NAME'] . ") ON DELETE " . ($fk['DELETE_RULE'] ?? 'NO ACTION') . "\n";
}

echo "\n=== PASSWORD_RESET_TOKENS TABLE ===\n";
$cols = $pdo->query("SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='school_lms' AND TABLE_NAME='password_reset_tokens' ORDER BY ORDINAL_POSITION")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "  " . $c['COLUMN_NAME'] . " " . $c['DATA_TYPE'] . ($c['CHARACTER_MAXIMUM_LENGTH'] ? "(" . $c['CHARACTER_MAXIMUM_LENGTH'] . ")" : "") . " " . ($c['IS_NULLABLE'] === 'YES' ? 'NULL' : 'NOT NULL') . " default=" . var_export($c['COLUMN_DEFAULT'], true) . "\n";
}

echo "\n=== PASSWORD_RESET_TOKENS INDEXES ===\n";
$idxs = $pdo->query("SELECT INDEX_NAME, COLUMN_NAME, NON_UNIQUE FROM information_schema.STATISTICS WHERE TABLE_SCHEMA='school_lms' AND TABLE_NAME='password_reset_tokens' ORDER BY INDEX_NAME, SEQ_IN_INDEX")->fetchAll(PDO::FETCH_ASSOC);
foreach ($idxs as $i) {
    echo "  " . $i['INDEX_NAME'] . " on " . $i['COLUMN_NAME'] . " " . (!$i['NON_UNIQUE'] ? 'UNIQUE' : 'INDEX') . "\n";
}

echo "\n=== SESSIONS TABLE ===\n";
$cols = $pdo->query("SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='school_lms' AND TABLE_NAME='sessions' ORDER BY ORDINAL_POSITION")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "  " . $c['COLUMN_NAME'] . " " . $c['DATA_TYPE'] . ($c['CHARACTER_MAXIMUM_LENGTH'] ? "(" . $c['CHARACTER_MAXIMUM_LENGTH'] . ")" : "") . " " . ($c['IS_NULLABLE'] === 'YES' ? 'NULL' : 'NOT NULL') . " default=" . var_export($c['COLUMN_DEFAULT'], true) . "\n";
}

echo "\n=== SESSIONS INDEXES ===\n";
$idxs = $pdo->query("SELECT INDEX_NAME, COLUMN_NAME, NON_UNIQUE FROM information_schema.STATISTICS WHERE TABLE_SCHEMA='school_lms' AND TABLE_NAME='sessions' ORDER BY INDEX_NAME, SEQ_IN_INDEX")->fetchAll(PDO::FETCH_ASSOC);
foreach ($idxs as $i) {
    echo "  " . $i['INDEX_NAME'] . " on " . $i['COLUMN_NAME'] . " " . (!$i['NON_UNIQUE'] ? 'UNIQUE' : 'INDEX') . "\n";
}

echo "\n=== SESSIONS FOREIGN KEYS ===\n";
$fks = $pdo->query("SELECT kcu.CONSTRAINT_NAME, kcu.COLUMN_NAME, kcu.REFERENCED_TABLE_NAME, kcu.REFERENCED_COLUMN_NAME, rc.DELETE_RULE FROM information_schema.KEY_COLUMN_USAGE kcu LEFT JOIN information_schema.REFERENTIAL_CONSTRAINTS rc ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME AND kcu.TABLE_SCHEMA = rc.CONSTRAINT_SCHEMA WHERE kcu.TABLE_SCHEMA='school_lms' AND kcu.TABLE_NAME='sessions' AND kcu.REFERENCED_TABLE_NAME IS NOT NULL ORDER BY kcu.CONSTRAINT_NAME, kcu.COLUMN_NAME")->fetchAll(PDO::FETCH_ASSOC);
foreach ($fks as $fk) {
    echo "  " . $fk['CONSTRAINT_NAME'] . ": " . $fk['COLUMN_NAME'] . " -> " . $fk['REFERENCED_TABLE_NAME'] . "(" . $fk['REFERENCED_COLUMN_NAME'] . ") ON DELETE " . ($fk['DELETE_RULE'] ?? 'NO ACTION') . "\n";
}
?>