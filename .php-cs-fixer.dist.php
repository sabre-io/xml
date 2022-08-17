<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->in(__DIR__)
    ->append([
        __FILE__,
    ]);

$config = new PhpCsFixer\Config();
$config->setRules([
    '@PSR1' => true,
    '@Symfony' => true,
]);
$config->setFinder($finder);

return $config;
