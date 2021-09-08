<?php

/**
 * @since 0.2.0
 */
return [
    'name' => 'Maker',
    'description' => 'Create component via Console (BETA)',
    'routes' => [
        'maker:maker' => [
            'controller'    => '\\sszcore\\console\\controllers\\Maker\\MakerController',
            'method'        => 'run',
            'description'   => 'Create Component --classname= --template='
        ],
        'maker:create-console-controller' => [
            'controller'    => '\\sszcore\\console\\controllers\\Maker\\CreateConsoleControllerController',
            'method'        => 'run',
            'description'   => 'Create Component --classname= --template=ConsoleController'
        ],
        'maker:create-app-controller' => [
            'controller'    => '\\sszcore\\console\\controllers\\Maker\\CreateAppControllerController',
            'method'        => 'run',
            'description'   => 'Create Component --classname= --template=ManagerController|Controller'
        ],
    ]
];