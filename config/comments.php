<?php

return [
    'mode' => \Bitw\Comments\LaravelComments::MODE_INJECT, // MODE_INJECT | MODE_WIDGET

    'only_auth' => false,

    'allow_url' => [
        //'/',
        '/'
    ],
    'allow_routes' => [
        'test.*',
    ],

    'frontend' => [
        'use' => 'jquery' // jquery || vue (In subsequent versions of development)
    ]
];