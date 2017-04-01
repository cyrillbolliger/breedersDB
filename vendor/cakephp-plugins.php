<?php
$baseDir = dirname(dirname(__FILE__));
return [
    'plugins' => [
        'Bake' => $baseDir . '/vendor/cakephp/bake/',
        'DebugKit' => $baseDir . '/vendor/cakephp/debug_kit/',
        'Migrations' => $baseDir . '/vendor/cakephp/migrations/',
        'SoftDelete' => $baseDir . '/vendor/pgbi/cakephp3-soft-delete/',
        'Xety/Cake3CookieAuth' => $baseDir . '/vendor/xety/cake3-cookieauth/'
    ]
];