<?php
/**
 * @author AlexanderC <self@alexanderc.me>
 * @date 11/13/13
 * @time 10:14 AM
 */

return [
    'dev' => [
        'entityPaths' => [],
        'connections' => [
            'slave' => [
                'cache' => null, // by default apc or file cache. You may provide Doctrine\Common\Cache\Cache instance
                'parameters' => [ // parameters to be used for dbal connection creation
                    'driver'   => 'pdo_mysql',
                    'user'     => 'root',
                    'password' => '',
                    'dbname'   => 'test',
                ]
            ],
            'master' => [
                'cache' => null,
                'parameters' => [
                    'driver'   => 'pdo_mysql',
                    'user'     => 'root',
                    'password' => '',
                    'dbname'   => 'test',
                ]
            ]
        ]
    ],
    'prod' => [
        'entityPaths' => [],
        'connections' => [
            'slave' => [
                'cache' => null,
                'parameters' => [
                    'driver'   => 'pdo_mysql',
                    'user'     => 'root',
                    'password' => '',
                    'dbname'   => 'test',
                ]
            ],
            'master' => [
                'cache' => null,
                'parameters' => [
                    'driver'   => 'pdo_mysql',
                    'user'     => 'root',
                    'password' => '',
                    'dbname'   => 'test',
                ]
            ]
        ]
    ]
];