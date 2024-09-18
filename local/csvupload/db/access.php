<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/csvupload:uploadcsv' => [
        'captype'   => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'admin' => CAP_ALLOW,
        ],
    ],
];
