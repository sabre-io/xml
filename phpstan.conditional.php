<?php

declare(strict_types=1);

/**
 * Add conditionally extra phpstan rules
 * Typically used for specific PHP versions
 * Must be called in main PHPStan neon config file.
 */
$includes = [];

if (PHP_VERSION_ID < 80400) {
    $includes[] = __DIR__.'/phpstan-rules-lt-8.4.neon';
}

$config = [];
$config['includes'] = $includes;
$config['parameters']['phpVersion'] = PHP_VERSION_ID;

return $config;
