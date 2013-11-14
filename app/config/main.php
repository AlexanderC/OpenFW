<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 3:04 PM
 */

return [
    'dev' => [
        'debug' => true, // debug mode flag
        'bundles' => [
            'logger' => [
                'class' => "OpenFWLoggerBundle\\Bundle",
                'lazy'  => false, // init bundle only when called through service container(call initLazy first)
                'data'  => null // data to be injected into bundle
            ],
            'translator' => [
                'class' => "OpenFWTranslatorBundle\\Bundle",
            ],
            'orm' => [
                'class' => "OpenFWORMBundle\\Bundle",
            ],
            'twig' => [
                'class' => "OpenFWTwigBundle\\Bundle"
            ]
        ]
    ],
    'prod' => [
        'debug' => false,
        'bundles' => [
            'logger' => [
                'class' => "OpenFWLoggerBundle\\Bundle", // full bundle class name
                'lazy'  => false, // init bundle only when called through service container(call initLazy first)
                'data'  => null // data to be injected into bundle
            ],
            'translator' => [
                'class' => "OpenFWTranslatorBundle\\Bundle",
            ],
            'orm' => [
                'class' => "OpenFWORMBundle\\Bundle",
            ],
            'twig' => [
                'class' => "OpenFWTwigBundle\\Bundle"
            ]
        ]
    ]
];