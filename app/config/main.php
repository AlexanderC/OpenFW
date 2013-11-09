<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/8/13
 * @time 3:04 PM
 */

return [
    'dev' => [
        'debug' => true,
        'bundles' => [
            'acme' => [
                'class' => "AcmeOpenFWBundle\\Bundle",
                'lazy'  => false
            ]
        ]
    ],
    'prod' => [
        'debug' => false,
        'bundles' => [
            // your bundles here
        ]
    ]
];