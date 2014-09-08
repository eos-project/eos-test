#!/usr/bin/env php
<?php

require __DIR__ . '/EosStaticFacade.php';

EosStaticFacade::init('127.0.0.1', null, 'test', '');

// Logging
EosStaticFacade::richLog(
    [
        'message' => 'This is log message, sent by CLI EOS testing tool',
        'custom'  => 'Something',
        'expose'  => ';)'
    ],
    [
        'test',
        'cli',
        mt_rand(1, 5)
    ]
);

EosStaticFacade::richLog(
    new InvalidArgumentException('Any text'),
    [
        'test',
        'cli'
    ]
);