<?php

$link = __DIR__ . '/storage';

echo "Exists: ";
var_dump(file_exists($link));

echo "<br>Is Link: ";
var_dump(is_link($link));

echo "<br>Target: ";
var_dump(readlink($link));