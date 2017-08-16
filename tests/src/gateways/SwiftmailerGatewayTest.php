<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/7
 * Time: 下午6:01
 */

namespace yiiunit\extensions\messenger\src\gateways;

use yii\messenger\Dispatcher;
use yii\messenger\gateways\QCloudSmsGateway;
use yii\messenger\messages\EmailMessage;
use yii\messenger\messages\SmsMessage;
use yii\messenger\Messenger;
use yiiunit\extensions\messenger\TestCase;

class SwiftmailerGatewayTest extends TestCase
{
    /**
     * @var Dispatcher
     */
    private $message;

    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        $this->message = \Yii::$app->get(Messenger::COMPONENT_NAME);
    }

    function testSendWithHmtlTemplate()
    {
        $msg = new EmailMessage([
            'to'=>'qsli@1ping.com',
            'isHtml'=>true,
            'extra' => [
                'email'=> 'qsli@1ping.com',
                'url'=> 'http://1ping.com',
            ],
            'template'=>'register',
        ]);
        $this->message->getMessenger()->message($msg)->send();
    }

    function testSendWithoutTemple()
    {
        $msg = new EmailMessage([
            'to' => '21997272@qq.com',
            'isHtml' => true,
            'extra' => [
                'email' => '21997272@qq.com',
                'url' => 'http://1ping.com',
            ],
            'content' => 'hello moto',
        ]);
        $this->message->getMessenger()->message($msg)->send();
    }
}
