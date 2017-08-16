<?php

use yii\web\Response;

$params = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);
$db = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/db.php'),
    require(__DIR__ . '/db-local.php')
);

$config = [
    'id' => 'message',
    'params' => $params,
    'basePath' => dirname(__DIR__),
    'defaultRoute'=>'admin/index',
    'language' => 'en',
    'sourceLanguage' => 'zh-CN',
    'timeZone'=>'Asia/Shanghai',
    'on beforeAction' => ['yakunit\message\SiteEvent', 'beforeAction'],
    'on beforeRequest' => ['yakunit\message\SiteEvent', 'beforeRequest'],
    'bootstrap' => [
        'log',
        [
            'class' => 'yii\filters\ContentNegotiator',
            'formats' => [
                'text/html' => Response::FORMAT_HTML,
                'application/json' => Response::FORMAT_JSON,
                'application/xml' => Response::FORMAT_XML,
            ],
            'languages' => [
                'en',
                'de',
            ],
        ],
    ],
    'components' => [
        'db' => $db,
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'iOcTLfWcevXSGqTMuxcHlBYcdhzmQOIw',
            'enableCookieValidation' => true,
            'enableCsrfValidation' => false, //nodejs开发时为false
            'parsers'=>[
                'application/json' => 'yii\web\JsonParser'
            ],
        ],
        'response' => [
            'format' => Response::FORMAT_HTML,
        ],
        'user' => [
            'identityClass' => 'yakunit\message\ContextUser',
            'enableAutoLogin' => true,//if web you can set true,api must set false
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'maxFileSize' => 200,
                    'levels' => [],
                    'logVars' => [],
                    'logFile' => '@runtime/logs/' . date('ymd') . '.log',
                ],

            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => require (__DIR__ . '/routes.php'),
        ],
        'assetManager' => [
            'forceCopy' => true
        ],
        'idGenerator'=>[
            'class'=>'yak\platform\components\SnowflakeIdGenerator',
        ],
        'authManager' => [
            'class' => 'yakunit\message\AuthManager',
        ],
        'cache' =>[
            'class'=>\yii\caching\FileCache::className(),
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => '',
                'username' => '',
                'password' => '',
                'port' => '',
                'encryption' => '',
            ],
            'messageConfig' => [
                'charset' => 'UTF-8',
            ]
        ],
        'message' => [
            'class' => 'yii\messenger\Dispatcher',
            'channels' => [
                'sms' => [
                    //短信通道
                    'class' => 'yii\messenger\channels\SmsChannel',
                    //发送策略
                    'strategy' => [
                        'class' => 'yii\messenger\strategies\OrderStrategy',
                    ],
                    'gateways' => [
                        'qcloud'
                    ],
                    'gatewayProviders' => [
                        //腾讯云
                        'qcloud' => [
                            'class' => 'yii\messenger\gateways\QCloudSmsGateway',
                            'appId' => '',
                            'appKey' => '',
                            'sign' => '',
                            'templates' => [
                                'verifyCode' => '10085',
                            ],
                        ],
                    ],
                ],
                'email' => [
                    //短信通道
                    'class' => 'yii\messenger\channels\EmailChannel',
                    //发送策略
                    'strategy' => [
                        'class' => 'yii\messenger\strategies\OrderStrategy',
                    ],
                    'gateways' => [
                        'swiftmailer'
                    ],
                    'gatewayProviders' => [
                        //腾讯云
                        'swiftmailer' => [
                            'class' => 'yii\messenger\gateways\SwiftmailerGateway',
                            'templates' => [
                                //支持别名
                                'register' => '@yak/message/views/layouts/email-register',
                            ],
                        ],
                    ],
                ]
            ],
        ],
    ],
    'aliases' => [
        '@bower' => __DIR__ . '/../../vendor/bower',
    ],
    'modules' => [
        'yakmessage' => [
            'class' => 'yak\message\Module',
        ]
    ],
];
return $config;