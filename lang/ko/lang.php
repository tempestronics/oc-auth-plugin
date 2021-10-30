<?php

return [
    'plugin' => [
        'name'        => '인증',
        'description' => 'API에 인증 미들웨어 제공'
    ],
    'settings' => [
        'auth_mechanism_label' => '인증 메커니즘'
    ],
    'mechanisms' => [
        'open' => [
            'label' => '열려있는'
        ]
    ]
];
