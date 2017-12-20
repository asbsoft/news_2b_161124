<?php
// route without prefix => controller/action without current (and parent) module(s) IDs
return [
    'view/<id:\d+>'               => 'main/view',         // must be before
    'view/<slug:[a-z0-9\-]+>'     => 'main/view-by-slug', // must be after
    '<action:(list)>/<page:\d+>'  => 'main/<action>',
    '<action:(index|list)>'       => 'main/<action>',
    '?'                           => 'main/index',
];
