<?php

$link = __DIR__ . '/storage';
$target = dirname(__DIR__) . '/storage/app/public';

if (file_exists($link) || is_link($link)) {
    unlink($link);
}

if (symlink($target, $link)) {
    echo "Symlink created successfully";
} else {
    echo "Failed to create symlink";
}