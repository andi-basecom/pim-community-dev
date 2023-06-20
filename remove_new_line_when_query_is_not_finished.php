<?php

if (file_exists("slow.txt")) {
    unlink("slow.txt");
}

$buffer = '';
$handle = fopen("mysql-slow.log", "r");
if (!$handle) {
    throw new \Exception('File not found');
}

while (($line = fgets($handle)) !== false) {
    if (empty($buffer)) {
        $buffer .= rtrim($line);
    } else {
        $buffer .= rtrim(" $line");
    }

    if (str_ends_with($line, ";\n")){
        file_put_contents('slow.txt', "$buffer\n", FILE_APPEND);
        $buffer='';
    }
}

fclose($handle);
file_put_contents('slow.txt', "$buffer\n", FILE_APPEND);
