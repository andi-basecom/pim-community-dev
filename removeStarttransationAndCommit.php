<?php

if (file_exists("slow.txt")) {
    unlink("slow.txt");
}

$buffer = '';
$handle = fopen("mysql-slow.log", "r");
if (!$handle) {
    throw new \Exception('File not found');
}

$previousLineIsStartTransaction = false;
while (($line = fgets($handle)) !== false) {
    if ($line === "START TRANSACTION;\n") {
        $previousLineIsStartTransaction = true;
        continue;
    }

    if ($previousLineIsStartTransaction && $line === "COMMIT;\n") {
        $previousLineIsStartTransaction = false;
        continue;
    }

    if ($previousLineIsStartTransaction) {
        file_put_contents('slow.txt', "START TRANSACTION;\n", FILE_APPEND);
    }

    $previousLineIsStartTransaction = false;
    file_put_contents('slow.txt', $line, FILE_APPEND);
}

fclose($handle);
