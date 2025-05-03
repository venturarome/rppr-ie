<?php

require_once 'Database.php';
require_once 'InputFile.php';

$db = Database::get();
$file = InputFile::get();

$it = 0;
while ($property = $file->next()) {
    $db->insert($property);
    if (++$it % 1000 === 0) {
        echo "Processed $it properties\n";
    }
}