<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "gs_monitor_observer"
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF['gs_monitor_observer'] = [
    'title' => 'GroundStack - Monitor Observer',
    'description' => '',
    'category' => 'plugin',
    'author' => 'Christian Hackl',
    'author_email' => 'info@groundstack.de',
    'state' => 'beta',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-9.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'GroundStack\\GsMonitorObserver\\' => 'Classes',
        ],
    ],
];
