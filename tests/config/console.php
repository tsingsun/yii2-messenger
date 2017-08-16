<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2016/12/29
 * Time: 下午3:21
 */
$params=[];
$db = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/db.php'),
    require(__DIR__ . '/db-local.php')
);
return [
    'id' => 'yii-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone' => 'Asia/shanghai',
    'controllerMap' => [
        'job' => [
            'class' => 'yii\messenger\consoles\JobController',
            'queue' => 'queue',
            'isolate' => false,
        ],
    ],
    'components' => [
        'log' => [
            'flushInterval' => 1,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
//                    'levels' => ['error', 'warning','trace','info'],
                    //控制台一般不需要服务器信息
                    'logVars' => [],
                    'exportInterval' => 1,
                ],
            ],
        ],
        'db' => $db,
        //发邮件
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => '',
                'username' => '',
                'password' => '',
                'port' => '',
                'encryption' => '',
            ],
            'messageConfig'=>[
                'charset'=>'UTF-8',
            ]
        ],
        'messenger' => [
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
                                'register' => '@yiiunit/extensions/messenger/data/email-register',
                            ],
                        ],
                    ],
                ],
                'queue' => [
                    'class' => 'yii\messenger\channels\JobQueueChannel',
                    //component key
                    'queue' => 'queue',
                ],
            ],
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '',
            'port' => '',
            'database' => 0,
        ],
        'queue' => [
            'class' => 'yii\queue\redis\Queue',
            'as log' => 'yii\queue\LogBehavior',
            'redis' => 'redis',
            'channel' => 'queue',

        ],
    ],
    'modules'=>[
    ],
    'params' => $params,
];