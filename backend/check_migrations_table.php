<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=school_lms", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$rows = $pdo->query("SELECT id, migration, batch FROM migrations ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
echo "MIGRATIONS TABLE ROWS: " . count($rows) . "\n";
foreach ($rows as $row) {
    echo "  id=" . $row["id"] . " migration=" . $row["migration"] . " batch=" . $row["batch"] . "\n";
}
?>