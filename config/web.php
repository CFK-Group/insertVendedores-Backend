<?php

$params = require(__DIR__ . '/params.php');


$config = [
    'id' => 'Insert',
    'name' => 'Insert',
    'defaultRoute' => 'site/index',
    'language' => 'es-ES',
    'charset' => 'utf-8',
    'timeZone' => 'America/Santiago',
    'language' => 'es-ES',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => ['@adminlte/widgets'=>'@vendor/adminlte/yii2-widgets'],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '1bVLEm3hPfBPfk103ONSysaOYZvOZXqz',
			'parsers' => 	[
								'application/json' => 'yii\web\JsonParser',
							]
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\Vendedor',
            'enableAutoLogin' => false,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 4 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info', 'trace'],
                	'logFile' => '@app/runtime/logs/custom.log',
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        
        'urlManager' => [
            'enablePrettyUrl' => true,
			'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                [
            		'class' => 'yii\rest\UrlRule',
            		'controller' => 'vendedor',
            		'extraPatterns' => 	[
            				'POST createUser' => 'createuser',
            				'POST createProspecto' => 'createprospecto',
            				'POST changeAction' => 'changeaction',
            				'POST updateProspecto' => 'updateprospecto',
            				'POST addAccionComercial' => 'addaccioncomercial',
            				'POST pingLoc' => 'pingloc',
                            'POST pingLoc2' => 'pingloc2',
                            'OPTIONS pingLoc2' => 'pingloc2',
            				'GET login' => 'login',
            				'GET search' => 'search',
            				'GET asdf' => 'asdf',
            				'GET getVentas' => 'getventas',
            				'GET getProspectos' => 'getprospectos',
                            'GET getVendedor' => 'getvendedor',
            		],
            	],
            	[
            		'class' => 'yii\rest\UrlRule',
            		'controller' => 'venta',
            		'extraPatterns' => 	[
            				'GET search' => 'search',
            				'GET /' => 'index',
            				'GET asdf' => 'asdf',
            				'GET getbyvendedor' => 'getbyvendedor',
            		],
            	],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'renta',
                    'extraPatterns' => 	[
                        'GET /' => 'index',
                        'GET calculoRenta' => 'calcularrenta'
                    ],
                ],
            	[
            		'class' => 'yii\rest\UrlRule',
            		'controller' => 'admin',
            		'extraPatterns' => 	[
                		    //URL => action from 'controller' to execute
            				'GET uploadventas' => 'uploadcsvventa',
            				'POST uploadventas' => 'uploadcsvventa',
                            'GET uploadprospectos' => 'uploadcsvprospectos',
                            'POST uploadprospectos' => 'uploadcsvprospectos',
                            'GET uploadusers' => 'uploadusers',
                            'POST uploadusers' => 'uploadusers',
                            'POST uploadusersdir' => 'uploadusersdir',
                            'GET direcciones' => 'getprospectos',
                            'GET exceldirecciones' => 'createexcelprospectos',
                            'GET createuser' => 'createuser',
                            'GET users' => 'getusers',
                            'GET excelusers' => 'createexcelusers',
                            'GET /' => 'index',
                            'GET ventasperdidas' => 'ventasperdidas',
                            'GET reporteusers' => 'reporteusers',
                            'GET reportedirecciones' => 'reportedirecciones',
                            'GET resumedata' => 'resumedata',
                            //'POST exceltest' => 'exceltest'
            		],
            	],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'site',
                    'extraPatterns' => 	[
                        //URL => action from 'controller' to execute
                    	'GET /' => 'login',
                    	'POST logout' => 'logout',
                        'GET login' => 'login',
                        'POST login' => 'login',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'chat',
                    'extraPatterns' => 	[
                        //URL => action from 'controller' to execute
                        'GET /' => 'index',
                        'GET msgs' => 'getmsgs',
                    ],
                ],
			],
		],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
	
		'allowedIPs' => ['*'],
		'newFileMode'=>0666,
		'newDirMode'=>0777,
    ];
}

return $config;
