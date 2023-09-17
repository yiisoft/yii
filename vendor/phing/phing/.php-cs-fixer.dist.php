<?php

$finder = PhpCsFixer\Finder::create()
    ->in('src/Phing')
    ->in('tests/Phing')
;

$config = new PhpCsFixer\Config();
return $config->setRules([
        '@PSR12' => true,
        '@PHP80Migration' => true,
        '@PhpCsFixer' => true,
        'concat_space' => ['spacing'=>'one'],
        'ordered_imports' => [
            'imports_order' => [
                'class', 'function', 'const',
            ],
            'sort_algorithm' => 'alpha',
        ]
    ])
    ->setFinder($finder)
;
