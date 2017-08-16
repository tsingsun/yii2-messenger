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
use yii\messenger\messages\SmsMessage;
use yii\messenger\Messenger;
use yiiunit\extensions\messenger\TestCase;

class QCloudSmsTest extends TestCase
{
    /**
     * @var Dispatcher
     */
    private $messenger;

    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        $this->messenger = \Yii::$app->get(Messenger::COMPONENT_NAME);
    }

    function testSendWithOutTemplate()
    {
        $msg = new SmsMessage([
            'to'=>'18659265199',
            'extra' => ['8876', '30秒'],
            'template'=>'verifyCode',
        ]);
        $this->messenger->on(Dispatcher::EVENT_SEND_ERROR,function ($event){
            $this->assertFalse($event->sendResult->status);
        });
        $val = $this->messenger->getMessenger()->message($msg)->send();
        $this->assertEmpty($val);
    }
}
