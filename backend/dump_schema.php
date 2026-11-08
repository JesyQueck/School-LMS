<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=school_lms", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get all tables
$tables = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='school_lms' ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN);
echo "TABLES IN DATABASE:" . PHP_EOL;
foreach ($tables as $t) echo "  - " . $t . PHP_EOL;
echo PHP_EOL . "Total: " . count($tables) . PHP_EOL;

// Get all columns for each table
foreach ($tables as $table) {
    echo PHP_EOL . "=== TABLE: " . $table . " ===" . PHP_EOL;
    $cols = $pdo->query("SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='school_lms' AND TABLE_NAME='$table' ORDER BY ORDINAL_POSITION")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        $type = $c['DATA_TYPE'];
        if ($c['CHARACTER_MAXIMUM_LENGTH']) $type .= '(' . $c['CHARACTER_MAXIMUM_LENGTH'] . ')';
        if ($c['NUMERIC_PRECISION'] && $c['NUMERIC_SCALE']) $type .= '(' . $c['NUMERIC_PRECISION'] . ',' . $c['NUMERIC_SCALE'] . ')';
        echo "  " . $c['COLUMN_NAME'] . " " . $type . " " . ($c['IS_NULLABLE'] === 'YES' ? 'NULL' : 'NOT NULL') . " default=" . var_export($c['COLUMN_DEFAULT'], true) . PHP_EOL;
    }
    
    // Get indexes
    $idxs = $pdo->query("SELECT INDEX_NAME, COLUMN_NAME, NON_UNIQUE FROM information_schema.STATISTICS WHERE TABLE_SCHEMA='school_lms' AND TABLE_NAME='$table' ORDER BY INDEX_NAME, SEQ_IN_INDEX")->fetchAll(PDO::FETCH_ASSOC);
    if ($idxs) {
        echo "  INDEXES:" . PHP_EOL;
        $currentIdx = null;
        $cols = [];
        $currentUnique = true;
        foreach ($idxs as $idx) {
            if ($currentIdx !== $idx['INDEX_NAME']) {
                if ($currentIdx !== null) echo "    " . $currentIdx . ": " . implode(', ', $cols) . " (" . (!$currentUnique ? 'UNIQUE' : 'INDEX') . ")" . PHP_EOL;
                $currentIdx = $idx['INDEX_NAME'];
                $currentUnique = !$idx['NON_UNIQUE'];
                $cols = [];
            }
            $cols[] = $idx['COLUMN_NAME'];
        }
        if ($currentIdx !== null) echo "    " . $currentIdx . ": " . implode(', ', $cols) . " (" . (!$currentUnique ? 'UNIQUE' : 'INDEX') . ")" . PHP_EOL;
    }
    
    // Get foreign keys via REFERENTIAL_CONSTRAINTS
    $fks = $pdo->query("SELECT kcu.CONSTRAINT_NAME, kcu.COLUMN_NAME, kcu.REFERENCED_TABLE_NAME, kcu.REFERENCED_COLUMN_NAME, rc.DELETE_RULE FROM information_schema.KEY_COLUMN_USAGE kcu LEFT JOIN information_schema.REFERENTIAL_CONSTRAINTS rc ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME AND kcu.TABLE_SCHEMA = rc.CONSTRAINT_SCHEMA WHERE kcu.TABLE_SCHEMA='school_lms' AND kcu.TABLE_NAME='$table' AND kcu.REFERENCED_TABLE_NAME IS NOT NULL ORDER BY kcu.CONSTRAINT_NAME, kcu.COLUMN_NAME")->fetchAll(PDO::FETCH_ASSOC);
    if ($fks) {
        echo "  FOREIGN KEYS:" . PHP_EOL;
        foreach ($fks as $fk) {
            echo "    " . $fk['CONSTRAINT_NAME'] . ": " . $fk['COLUMN_NAME'] . " -> " . $fk['REFERENCED_TABLE_NAME'] . "(" . $fk['REFERENCED_COLUMN_NAME'] . ") ON DELETE " . ($fk['DELETE_RULE'] ?? 'NO ACTION') . PHP_EOL;
        }
    }
}
?>