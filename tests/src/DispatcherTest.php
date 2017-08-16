<?php
/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/8/7
 * Time: 下午1:46
 */

namespace yiiunit\extensions\messenger\src;

use yii\messenger\Dispatcher;
use yii\messenger\messages\EmailMessage;
use yii\messenger\Messenger;
use yiiunit\extensions\messenger\TestCase;

class DispatcherTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testConstruct()
    {
        $message = \Yii::$app->get(Messenger::COMPONENT_NAME);
        $this->assertInstanceOf(Dispatcher::className(), $message, '可以先确认对应的配置文件是否正确');
    }

    public function testBeforeSendEvent()
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = \Yii::$app->get(Messenger::COMPONENT_NAME);
        $msgFor = null;
        $dispatcher->on(Dispatcher::EVENT_BEFORE_SEND, function ($event) use (&$msgFor) {
            $event->handled = false;
            $msgFor = $event->message;
        });
        $message = new EmailMessage(['to' => 'qsli@test.com']);
        $dispatcher->dispatch([$message]);
        $this->assertEquals($message, $msgFor);
    }

    public function testAfterSendEvent()
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = \Yii::$app->get(Messenger::COMPONENT_NAME);
        $msgFor = null;
        $dispatcher->on(Dispatcher::EVENT_AFTER_SEND, function ($event) use (&$msgFor) {
            $event->handled = false;
            $msgFor = $event->message;
        });
        $message = new EmailMessage(['to' => 'qsli@test.com']);
        $dispatcher->dispatch([$message]);
        $this->assertEquals($message, $msgFor);
    }

    public function testSendToQueue()
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = \Yii::$app->get(Messenger::COMPONENT_NAME);
        $dispatcher->sendToQueue = true;
        $messages = [new EmailMessage(['to' => 'qsli@1ping.com'])];
        $dispatcher->dispatch($messages);
    }
}
