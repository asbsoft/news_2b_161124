<?php
return [
    'label'   => 'News manager 2-b',
    'version' => '2b.171028',
    'origin'  => 'news_2b_161124',

    /** Temporary folder for processing archieves */
    'tmpDir' => '@runtime/news-v2-tmp',
    
    /** @var integer Time (in hours) to save exported news in temporary area. If 0 clear all */
    'gcExportOutOfDateHours' => 0,

    /** Max size of uploaded archieve. Also limited in PHP.ini/server tunes */
    'maxImportArchSize' => 2100000, //bytes

];
