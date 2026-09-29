<?php

$target = __DIR__ . '/storage/app/public';
$link = __DIR__ . '/public/storage';



$link = __DIR__ . '/storage';

var_dump(file_exists($link));
var_dump(is_link($link));

