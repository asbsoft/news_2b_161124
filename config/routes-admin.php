<?php
// route without prefix => controller/action without current (and parent) module(s) IDs
return [

    '<action:(view|update|delete|change-visible|export)>/<id:\d+>'
                                        => 'admin/<action>',
    '<action:(index)>/<page:\d+>'       => 'admin/<action>',
    '<action:(index|create|import)>'    => 'admin/<action>',
    'el-finder/connector/<id:\d+>'      => 'el-finder/connector',
  //'el-finder/connector'               => 'el-finder/connector',
    '?'                                 => 'admin/index',
];
