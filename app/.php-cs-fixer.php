<?php


$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/Broker')
    ->in(__DIR__ . '/Console')
    ->in(__DIR__ . '/Event')
    ->in(__DIR__ . '/Persistence')
    ->in(__DIR__ . '/Worker')
    ->name('*.php')
    ->notName('.php-cs-fixer.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'declare_strict_types' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
    ])
    ->setFinder($finder);
