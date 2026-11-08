<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=school_lms", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1. sessions table columns
echo "=== SESSIONS TABLE COLUMNS ===\n";
$cols = $pdo->query("SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='school_lms' AND TABLE_NAME='sessions' ORDER BY ORDINAL_POSITION")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    $type = $c["DATA_TYPE"];
    if ($c["CHARACTER_MAXIMUM_LENGTH"]) $type .= "(" . $c["CHARACTER_MAXIMUM_LENGTH"] . ")";
    if ($c["NUMERIC_PRECISION"] && $c["NUMERIC_SCALE"]) $type .= "(" . $c["NUMERIC_PRECISION"] . "," . $c["NUMERIC_SCALE"] . ")";
    echo "  " . $c["COLUMN_NAME"] . " " . $type . " " . ($c["IS_NULLABLE"] === "YES" ? "NULL" : "NOT NULL") . " default=" . var_export($c["COLUMN_DEFAULT"], true) . "\n";
}

// 2. sessions indexes
echo "\n=== SESSIONS INDEXES ===\n";
$idxs = $pdo->query("SELECT INDEX_NAME, COLUMN_NAME, NON_UNIQUE FROM information_schema.STATISTICS WHERE TABLE_SCHEMA='school_lms' AND TABLE_NAME='sessions' ORDER BY INDEX_NAME, SEQ_IN_INDEX")->fetchAll(PDO::FETCH_ASSOC);
foreach ($idxs as $i) echo "  " . $i["INDEX_NAME"] . " on " . $i["COLUMN_NAME"] . " " . (!$i["NON_UNIQUE"] ? "UNIQUE" : "INDEX") . "\n";

// 3. sessions foreign keys
echo "\n=== SESSIONS FOREIGN KEYS ===\n";
$fks = $pdo->query("SELECT kcu.CONSTRAINT_NAME, kcu.COLUMN_NAME, kcu.REFERENCED_TABLE_NAME, kcu.REFERENCED_COLUMN_NAME, rc.DELETE_RULE FROM information_schema.KEY_COLUMN_USAGE kcu LEFT JOIN information_schema.REFERENTIAL_CONSTRAINTS rc ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME AND kcu.TABLE_SCHEMA = rc.CONSTRAINT_SCHEMA WHERE kcu.TABLE_SCHEMA='school_lms' AND kcu.TABLE_NAME='sessions' AND kcu.REFERENCED_TABLE_NAME IS NOT NULL ORDER BY kcu.CONSTRAINT_NAME, kcu.COLUMN_NAME")->fetchAll(PDO::FETCH_ASSOC);
foreach ($fks as $fk) echo "  " . $fk["CONSTRAINT_NAME"] . ": " . $fk["COLUMN_NAME"] . " -> " . $fk["REFERENCED_TABLE_NAME"] . "(" . $fk["REFERENCED_COLUMN_NAME"] . ") ON DELETE " . ($fk["DELETE_RULE"] ?? "NO ACTION") . "\n";

// 4. users.id column
echo "\n=== USERS.ID COLUMN ===\n";
$cols = $pdo->query("SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='school_lms' AND TABLE_NAME='users' AND COLUMN_NAME='id'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    $type = $c["DATA_TYPE"];
    if ($c["CHARACTER_MAXIMUM_LENGTH"]) $type .= "(" . $c["CHARACTER_MAXIMUM_LENGTH"] . ")";
    if ($c["NUMERIC_PRECISION"] && $c["NUMERIC_SCALE"]) $type .= "(" . $c["NUMERIC_PRECISION"] . "," . $c["NUMERIC_SCALE"] . ")";
    echo "  " . $c["COLUMN_NAME"] . " " . $type . " " . ($c["IS_NULLABLE"] === "YES" ? "NULL" : "NOT NULL") . " default=" . var_export($c["COLUMN_DEFAULT"], true) . "\n";
}

// 5. Orphaned sessions count
echo "\n=== ORPHANED SESSIONS (user_id IS NOT NULL but no matching users.id) ===\n";
$count = $pdo->query("SELECT COUNT(*) FROM sessions s LEFT JOIN users u ON s.user_id = u.id WHERE s.user_id IS NOT NULL AND u.id IS NULL")->fetchColumn();
echo "  Orphaned sessions: " . $count . "\n";

// 6. Sessions with user_id IS NULL
echo "\n=== SESSIONS WITH user_id IS NULL ===\n";
$count = $pdo->query("SELECT COUNT(*) FROM sessions WHERE user_id IS NULL")->fetchColumn();
echo "  NULL user_id sessions: " . $count . "\n";

// 7. Total sessions
echo "\n=== TOTAL SESSIONS ===\n";
$count = $pdo->query("SELECT COUNT(*) FROM sessions")->fetchColumn();
echo "  Total sessions: " . $count . "\n";

// 8. Storage engines
echo "\n=== STORAGE ENGINES ===\n";
$engines = $pdo->query("SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA='school_lms' AND TABLE_NAME IN ('sessions', 'users')")->fetchAll(PDO::FETCH_ASSOC);
foreach ($engines as $e) echo "  " . $e["TABLE_NAME"] . ": " . $e["ENGINE"] . "\n";

// 9. Column signedness check
echo "\n=== COLUMN SIGNEDNESS CHECK ===\n";
$sessionsUserId = $pdo->query("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='school_lms' AND TABLE_NAME='sessions' AND COLUMN_NAME='user_id'")->fetchColumn();
$usersId = $pdo->query("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='school_lms' AND TABLE_NAME='users' AND COLUMN_NAME='id'")->fetchColumn();
echo "  sessions.user_id COLUMN_TYPE: " . $sessionsUserId . "\n";
echo "  users.id COLUMN_TYPE: " . $usersId . "\n";

// 10. Check user_id values range
echo "\n=== user_id VALUE RANGE ===\n";
$minMax = $pdo->query("SELECT MIN(user_id), MAX(user_id) FROM sessions WHERE user_id IS NOT NULL")->fetch();
echo "  MIN user_id: " . ($minMax[0] ?? "NULL") . "\n";
echo "  MAX user_id: " . ($minMax[1] ?? "NULL") . "\n";
echo "  users.id range: MIN=" . $pdo->query("SELECT MIN(id) FROM users")->fetchColumn() . " MAX=" . $pdo->query("SELECT MAX(id) FROM users")->fetchColumn() . "\n";
?>