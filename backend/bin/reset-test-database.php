<?php

declare(strict_types=1);

$projectDir = dirname(__DIR__);
$varDir = $projectDir.'/var';
$dbFile = $varDir.'/test.db';

if (!is_dir($varDir)) {
    mkdir($varDir, 0777, true);
}
if (is_file($dbFile)) {
    unlink($dbFile);
}
touch($dbFile);
