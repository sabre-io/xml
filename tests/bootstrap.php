<?php

$try = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php',
];

foreach ($try as $path) {
    if (file_exists($path)) {
        include $path;
        break;
    }
}

// Some extra classes
include __DIR__ . '/Sabre/Xml/Element/Mock.php';
include __DIR__ . '/Sabre/Xml/Element/Eater.php';
